<?php

namespace App\Http\Controllers\Tailoring;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $canMoney = (bool)$user?->can('payments.view');
        $today = today();

        $stages = DB::table('workflow_stages')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        $deliveredStage = $stages->first(fn($s) => strtoupper((string)$s->code) === 'DELIVERED') ?? $stages->last();
        $deliveredId = (int)($deliveredStage->id ?? 0);

        // ---- open (not completed) items ----
        $openItems = fn() => DB::table('job_batch_items as jbi')
            ->join('job_batches as jb', 'jb.id', '=', 'jbi.job_batch_id')
            ->whereNull('jbi.completed_at');

        // pieces / items per stage
        $stageStats = $openItems()
            ->whereNotNull('jbi.current_stage_id')
            ->groupBy('jbi.current_stage_id')
            ->select('jbi.current_stage_id', DB::raw('COUNT(*) as items'), DB::raw('COALESCE(SUM(jbi.qty),0) as pieces'))
            ->get()
            ->keyBy('current_stage_id');

        $stageChart = $stages->map(fn($s) => [
            'name' => $s->name,
            'pieces' => (int)($stageStats[$s->id]->pieces ?? 0),
            'items' => (int)($stageStats[$s->id]->items ?? 0),
            'is_delivery' => (int)$s->id === $deliveredId,
        ])->values();

        // ---- KPIs ----
        $readyPieces = (int)($stageStats[$deliveredId]->pieces ?? 0);
        $readyJobs = (int)$openItems()->where('jbi.current_stage_id', $deliveredId)->distinct()->count('jb.job_id');

        // jobs with at least one piece still being made (not yet at the delivery stage)
        $inProductionJobIds = $openItems()
            ->where(fn($w) => $w->whereNull('jbi.current_stage_id')->orWhere('jbi.current_stage_id', '!=', $deliveredId))
            ->distinct()
            ->pluck('jb.job_id');

        $kpi = [
            'active_jobs' => (int)$openItems()->distinct()->count('jb.job_id'),
            'pieces_in_production' => (int)$stageChart->where('is_delivery', false)->sum('pieces'),
            'ready_pieces' => $readyPieces,
            'ready_jobs' => $readyJobs,
            'overdue_jobs' => Job::whereIn('id', $inProductionJobIds)->whereDate('due_date', '<', $today)->count(),
            'due_week' => Job::whereIn('id', $inProductionJobIds)->whereBetween('due_date', [$today, $today->copy()->addDays(7)])->count(),
            'new_jobs_month' => Job::where('created_at', '>=', $today->copy()->startOfMonth())->count(),
        ];

        // ---- money (only for users who may see payments) ----
        $money = null;
        if ($canMoney) {
            $pending = Job::query()->pendingBills();

            $statusRow = DB::table('jobs')
                ->selectRaw(
                    'SUM(CASE WHEN ' . Job::sqlBalance() . ' <= 0.009 THEN 1 ELSE 0 END) AS paid,
                     SUM(CASE WHEN ' . Job::sqlBalance() . ' > 0.009 AND ' . Job::SQL_PAID_TOTAL . ' > 0 THEN 1 ELSE 0 END) AS partial,
                     SUM(CASE WHEN ' . Job::sqlBalance() . ' > 0.009 AND ' . Job::SQL_PAID_TOTAL . ' <= 0 THEN 1 ELSE 0 END) AS unpaid'
                )
                ->whereRaw(Job::SQL_ITEMS_TOTAL . ' > 0')
                ->first();

            $money = [
                'outstanding' => (float)(clone $pending)->sum(DB::raw(Job::sqlBalance())),
                'pending_count' => (clone $pending)->count(),
                'collected_month' => (float)DB::table('job_payments')->where('paid_at', '>=', $today->copy()->startOfMonth())->sum('amount'),
                'collected_today' => (float)DB::table('job_payments')->whereDate('paid_at', $today)->sum('amount'),
                'status' => [
                    'paid' => (int)($statusRow->paid ?? 0),
                    'partial' => (int)($statusRow->partial ?? 0),
                    'unpaid' => (int)($statusRow->unpaid ?? 0),
                ],
            ];
        }

        // ---- last 14 days: new jobs (+ money collected) ----
        $from = $today->copy()->subDays(13);
        $jobsPerDay = DB::table('jobs')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) d, COUNT(*) c')
            ->groupBy('d')
            ->pluck('c', 'd');
        $paidPerDay = $canMoney
            ? DB::table('job_payments')->where('paid_at', '>=', $from)->selectRaw('DATE(paid_at) d, SUM(amount) s')->groupBy('d')->pluck('s', 'd')
            : collect();

        $trend = ['labels' => [], 'jobs' => [], 'collected' => []];
        foreach (CarbonPeriod::create($from, $today) as $day) {
            $key = $day->toDateString();
            $trend['labels'][] = $day->format('d M');
            $trend['jobs'][] = (int)($jobsPerDay[$key] ?? 0);
            $trend['collected'][] = round((float)($paidPerDay[$key] ?? 0), 2);
        }

        // ---- tables ----
        $pendingPiecesSql = '(SELECT COALESCE(SUM(jbi.qty),0) FROM job_batch_items jbi JOIN job_batches jb ON jb.id = jbi.job_batch_id
            WHERE jb.job_id = jobs.id AND jbi.completed_at IS NULL AND (jbi.current_stage_id IS NULL OR jbi.current_stage_id <> ' . $deliveredId . '))';

        $overdueJobs = Job::query()
            ->whereIn('id', $inProductionJobIds)
            ->whereDate('due_date', '<', $today)
            ->with('customer')
            ->select('jobs.*')
            ->selectRaw("{$pendingPiecesSql} AS pending_pieces")
            ->orderBy('due_date')
            ->limit(6)
            ->get();

        $dueSoonJobs = Job::query()
            ->whereIn('id', $inProductionJobIds)
            ->whereBetween('due_date', [$today, $today->copy()->addDays(7)])
            ->with('customer')
            ->select('jobs.*')
            ->selectRaw("{$pendingPiecesSql} AS pending_pieces")
            ->orderBy('due_date')
            ->limit(6)
            ->get();

        // recent jobs with progress (pieces at delivery stage or completed / all pieces)
        $recentJobs = Job::query()
            ->with('customer')
            ->withBillTotals()
            ->selectRaw('(SELECT COALESCE(SUM(jbi.qty),0) FROM job_batch_items jbi JOIN job_batches jb ON jb.id = jbi.job_batch_id WHERE jb.job_id = jobs.id) AS total_pieces')
            ->selectRaw('(SELECT COALESCE(SUM(jbi.qty),0) FROM job_batch_items jbi JOIN job_batches jb ON jb.id = jbi.job_batch_id
                WHERE jb.job_id = jobs.id AND (jbi.completed_at IS NOT NULL OR jbi.current_stage_id = ' . $deliveredId . ')) AS done_pieces')
            ->latest()
            ->limit(6)
            ->get();

        // staff workload: pieces received in the last 30 days
        $staffWorkload = DB::table('handover_logs as hl')
            ->join('users as u', 'u.id', '=', 'hl.received_by')
            ->where('hl.handover_at', '>=', $today->copy()->subDays(30))
            ->groupBy('hl.received_by', 'u.name')
            ->select('u.name as staff_name', DB::raw('COUNT(*) as handovers'), DB::raw('COALESCE(SUM(hl.qty),0) as pieces'))
            ->orderByDesc('pieces')
            ->limit(5)
            ->get();

        return view('tailoring.dashboard.production', compact(
            'stageChart',
            'kpi',
            'money',
            'trend',
            'overdueJobs',
            'dueSoonJobs',
            'recentJobs',
            'staffWorkload'
        ));
    }
}
