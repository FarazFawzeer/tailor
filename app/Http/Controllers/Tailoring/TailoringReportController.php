<?php

namespace App\Http\Controllers\Tailoring;

use App\Http\Controllers\Controller;
use App\Models\WorkflowStage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TailoringReportController extends Controller
{
    /**
     * ✅ Report 1: Live Stage Dashboard (current items sitting in each stage)
     * Uses job_batch_items.current_stage_id
     */
    public function stages(Request $request)
    {
        $stages = WorkflowStage::query()
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get(['id','name','sort_order']);

        $from = $request->get('from');
        $to   = $request->get('to');
        $q    = trim((string)$request->get('q'));

        // Base query from job_batch_items, optionally filter by job/customer
        $base = DB::table('job_batch_items as i')
            ->join('job_batches as b', 'b.id', '=', 'i.job_batch_id')
            ->join('jobs as j', 'j.id', '=', 'b.job_id')
            ->leftJoin('customers as c', 'c.id', '=', 'j.customer_id')
            ->whereNull('i.completed_at')
            ->when($from, fn($qq) => $qq->whereDate('i.created_at', '>=', $from))
            ->when($to, fn($qq) => $qq->whereDate('i.created_at', '<=', $to))
            ->when($q, function($qq) use ($q){
                $qq->where('j.job_no', 'like', "%{$q}%")
                   ->orWhere('c.full_name', 'like', "%{$q}%")
                   ->orWhere('c.phone', 'like', "%{$q}%");
            });

        // Summary by current stage
        $rows = (clone $base)
            ->selectRaw('i.current_stage_id,
                        COUNT(*) as items_count,
                        COALESCE(SUM(i.qty),0) as qty_sum,
                        COALESCE(SUM(i.qty * COALESCE(i.unit_price,0)),0) as amount_sum')
            ->groupBy('i.current_stage_id')
            ->get();

        $stageSummary = $rows->keyBy('current_stage_id')->map(function($r){
            return [
                'items_count' => (int)$r->items_count,
                'qty_sum'     => (int)$r->qty_sum,
                'amount_sum'  => (float)$r->amount_sum,
            ];
        })->toArray();

        $totals = (clone $base)
            ->selectRaw('COUNT(*) as items_count,
                        COALESCE(SUM(i.qty),0) as qty_sum,
                        COALESCE(SUM(i.qty * COALESCE(i.unit_price,0)),0) as amount_sum')
            ->first();

        return view('tailoring.reports.stages', compact(
            'stages','stageSummary','totals','from','to','q'
        ));
    }

    /**
     * ✅ Report 2: Staff report (who moved how many qty into each stage)
     * Uses handover_logs.to_stage_id, qty, handed_over_by, handover_at
     * (Completion logs have to_stage_id = null, we ignore those)
     */
    public function staff(Request $request)
    {
        $stages = WorkflowStage::query()
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get(['id','name','sort_order']);

        $from    = $request->get('from');
        $to      = $request->get('to');
        $staffId = $request->get('staff_id');
        $q       = trim((string)$request->get('q')); // filter by job_no/customer (optional)

        // Base: handover logs (only real stage moves)
        $base = DB::table('handover_logs as h')
            ->join('users as u', 'u.id', '=', 'h.handed_over_by')
            ->join('job_batch_items as i', 'i.id', '=', 'h.job_batch_item_id')
            ->join('job_batches as b', 'b.id', '=', 'i.job_batch_id')
            ->join('jobs as j', 'j.id', '=', 'b.job_id')
            ->leftJoin('customers as c', 'c.id', '=', 'j.customer_id')
            ->whereNotNull('h.to_stage_id') // ignore completion logs
            ->when($from, fn($qq) => $qq->whereDate('h.handover_at', '>=', $from))
            ->when($to, fn($qq) => $qq->whereDate('h.handover_at', '<=', $to))
            ->when($staffId, fn($qq) => $qq->where('h.handed_over_by', $staffId))
            ->when($q, function($qq) use ($q){
                $qq->where('j.job_no', 'like', "%{$q}%")
                   ->orWhere('c.full_name', 'like', "%{$q}%")
                   ->orWhere('c.phone', 'like', "%{$q}%");
            });

        // Staff x Stage totals
        $rows = (clone $base)
            ->selectRaw('h.handed_over_by,
                        u.name as staff_name,
                        h.to_stage_id,
                        COALESCE(SUM(h.qty),0) as qty_sum,
                        COUNT(*) as moves_count,
                        COUNT(DISTINCT h.job_batch_item_id) as unique_items')
            ->groupBy('h.handed_over_by', 'u.name', 'h.to_stage_id')
            ->orderBy('u.name')
            ->get();

        // Staff dropdown list
        $staffList = User::query()->orderBy('name')->get(['id','name']);

        // Pivot for easy table display
        $matrix = [];
        foreach ($rows as $r) {
            $uid = (int)$r->handed_over_by;

            $matrix[$uid]['name'] = $r->staff_name;
            $matrix[$uid]['stages'][(int)$r->to_stage_id] = [
                'qty'         => (int)$r->qty_sum,
                'moves'       => (int)$r->moves_count,
                'uniqueItems' => (int)$r->unique_items,
            ];
        }

        return view('tailoring.reports.staff', compact(
            'stages','matrix','staffList','from','to','staffId','q'
        ));
    }
}