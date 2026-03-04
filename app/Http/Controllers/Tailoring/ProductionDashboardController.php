<?php

namespace App\Http\Controllers\Tailoring;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class ProductionDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1) Get active stages (Cut, Sewing, Button, Ironing, Packaging)
        $stages = DB::table('workflow_stages')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        // 2) Count items by stage (job_batch_items.current_stage_id)
        $stageCounts = DB::table('job_batch_items as jbi')
            ->select('jbi.current_stage_id', DB::raw('COUNT(*) as total_items'), DB::raw('SUM(jbi.qty) as total_qty'))
            ->whereNotNull('jbi.current_stage_id')
            ->groupBy('jbi.current_stage_id')
            ->pluck('total_items', 'current_stage_id');

        $stageQtyCounts = DB::table('job_batch_items as jbi')
            ->select('jbi.current_stage_id', DB::raw('SUM(jbi.qty) as total_qty'))
            ->whereNotNull('jbi.current_stage_id')
            ->groupBy('jbi.current_stage_id')
            ->pluck('total_qty', 'current_stage_id');

        // 3) Identify last stage (Ready for Delivery)
        $lastStage = $stages->last(); // assumes sort_order is correct
        $readyForDeliveryCount = 0;
        $readyForDeliveryQty = 0;

        if ($lastStage) {
            $readyForDeliveryCount = (int)($stageCounts[$lastStage->id] ?? 0);
            $readyForDeliveryQty = (int)($stageQtyCounts[$lastStage->id] ?? 0);
        }

        // 4) Overdue items (if jobs.due_date exists)
        $hasDueDate = Schema::hasColumn('jobs', 'due_date');
        $overdueItems = collect();
        $overdueCount = 0;

        if ($hasDueDate) {
            $overdueItems = DB::table('job_batch_items as jbi')
                ->join('job_batches as jb', 'jb.id', '=', 'jbi.job_batch_id')
                ->join('jobs as j', 'j.id', '=', 'jb.job_id')
                ->leftJoin('workflow_stages as ws', 'ws.id', '=', 'jbi.current_stage_id')
                ->leftJoin('dress_types as dt', 'dt.id', '=', 'jbi.dress_type_id')
                ->leftJoin('customers as c', 'c.id', '=', 'j.customer_id')
                ->whereNotNull('j.due_date')
                ->whereDate('j.due_date', '<', now()->toDateString())
                ->orderBy('j.due_date', 'asc')
                ->limit(10)
                ->select([
                    'j.job_no',
                    'jb.batch_no',
                    'c.full_name',
                    'dt.name as dress_name',
                    'jbi.qty',
                    'ws.name as stage_name',
                    'j.due_date',
                ])
                ->get();

            $overdueCount = DB::table('job_batch_items as jbi')
                ->join('job_batches as jb', 'jb.id', '=', 'jbi.job_batch_id')
                ->join('jobs as j', 'j.id', '=', 'jb.job_id')
                ->whereNotNull('j.due_date')
                ->whereDate('j.due_date', '<', now()->toDateString())
                ->count();
        }

        // 5) Latest production items
        $latestItems = DB::table('job_batch_items as jbi')
            ->join('job_batches as jb', 'jb.id', '=', 'jbi.job_batch_id')
            ->join('jobs as j', 'j.id', '=', 'jb.job_id')
            ->leftJoin('workflow_stages as ws', 'ws.id', '=', 'jbi.current_stage_id')
            ->leftJoin('dress_types as dt', 'dt.id', '=', 'jbi.dress_type_id')
            ->leftJoin('customers as c', 'c.id', '=', 'j.customer_id')
            ->orderBy('jbi.updated_at', 'desc')
            ->limit(5)
            ->select([
                'jbi.id',
                'j.job_no',
                'jb.batch_no',
                'c.full_name',
                'dt.name as dress_name',
                'jbi.qty',
                'ws.name as stage_name',
                'jbi.updated_at',
                'j.due_date',
            ])
            ->get();

        // 6) Staff workload (optional) - only if handover table exists
        $staffWorkload = collect();
        if (Schema::hasTable('handover_logs')) {
            $staffWorkload = DB::table('handover_logs as hl')
                ->leftJoin('users as u', 'u.id', '=', 'hl.received_by')
                ->leftJoin('workflow_stages as ws', 'ws.id', '=', 'hl.to_stage_id')
                ->select([
                    'u.name as staff_name',
                    'ws.name as stage_name',
                    DB::raw('COUNT(*) as handovers'),
                    DB::raw('SUM(hl.qty) as total_qty'),
                ])
                ->whereNotNull('hl.received_by')
                ->groupBy('hl.received_by', 'hl.to_stage_id', 'u.name', 'ws.name')
                ->orderByDesc('handovers')
                ->limit(5)
                ->get();
        }

        return view('tailoring.dashboard.production', compact(
            'stages',
            'stageCounts',
            'stageQtyCounts',
            'lastStage',
            'readyForDeliveryCount',
            'readyForDeliveryQty',
            'hasDueDate',
            'overdueCount',
            'overdueItems',
            'latestItems',
            'staffWorkload'
        ));
    }
}
