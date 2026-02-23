<?php

namespace App\Http\Controllers\Tailoring;

use App\Http\Controllers\Controller;
use App\Models\WorkflowStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkQueueController extends Controller
{
    private array $roleStageMap = [
        'cutter' => 'cut',
        'sewing' => 'sewing',
        'button' => 'button',
        'ironing' => 'ironing',
        'packaging' => 'packaging',
    ];

    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));
        $user = $request->user();

        $canViewAll = $user->hasAnyRole(['super_admin', 'admin', 'front_desk']);

        $stages = WorkflowStage::query()
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        $selectedStageId = $request->get('stage_id');

        // If not admin, force stage based on role
        if (!$canViewAll) {
            $stageCode = null;

            foreach ($this->roleStageMap as $role => $code) {
                if ($user->hasRole($role)) {
                    $stageCode = $code;
                    break;
                }
            }

            if (!$stageCode) {
                $items = collect();
                return view('tailoring.work_queue.index', compact('items', 'q', 'stages', 'selectedStageId', 'canViewAll'));
            }

            $forcedStage = $stages->firstWhere('code', $stageCode);
            $selectedStageId = $forcedStage?->id;
        } else {
            $selectedStageId = $selectedStageId ?: null;
        }

        /**
         * GROUPING LOGIC
         * We group by:
         * - group_id (root)
         * - job_batch_id
         * - dress_type_id
         * - current_stage_id
         * so partial rows become one "group row" per stage.
         */
        $query = DB::table('job_batch_items as jbi')
            ->join('job_batches as jb', 'jb.id', '=', 'jbi.job_batch_id')
            ->join('jobs as j', 'j.id', '=', 'jb.job_id')
            ->leftJoin('customers as c', 'c.id', '=', 'j.customer_id')
            ->leftJoin('dress_types as dt', 'dt.id', '=', 'jbi.dress_type_id')
            ->leftJoin('workflow_stages as ws', 'ws.id', '=', 'jbi.current_stage_id')
            ->selectRaw("
                COALESCE(jbi.parent_item_id, jbi.id) as group_id,
                jbi.job_batch_id,
                jbi.dress_type_id,
                jbi.current_stage_id,
                j.job_no,
                jb.batch_no,
                c.full_name as customer_name,
                dt.name as dress_name,
                ws.name as stage_name,
                MIN(jbi.updated_at) as first_updated_at,
                MAX(jbi.updated_at) as last_updated_at,
                SUM(jbi.qty) as total_qty,
                SUM(CASE WHEN jbi.completed_at IS NULL THEN 1 ELSE 0 END) as pending_rows
            ")
            ->whereNull('jbi.completed_at')
            ->when($selectedStageId, fn($qq) => $qq->where('jbi.current_stage_id', $selectedStageId))
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('j.job_no', 'like', "%{$q}%")
                      ->orWhere('jb.batch_no', 'like', "%{$q}%")
                      ->orWhere('c.full_name', 'like', "%{$q}%")
                      ->orWhere('dt.name', 'like', "%{$q}%");
                });
            })
            ->groupByRaw("
                COALESCE(jbi.parent_item_id, jbi.id),
                jbi.job_batch_id,
                jbi.dress_type_id,
                jbi.current_stage_id,
                j.job_no,
                jb.batch_no,
                c.full_name,
                dt.name,
                ws.name
            ")
            ->orderByDesc(DB::raw('MAX(jbi.updated_at)'));

        // Manual pagination for query builder
        $perPage = 15;
        $page = (int)($request->get('page', 1));
        $total = (clone $query)->count();
        $rows = $query->forPage($page, $perPage)->get();

        // simple paginator object (so blade can use links())
        $items = new \Illuminate\Pagination\LengthAwarePaginator(
            $rows,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('tailoring.work_queue.index', compact('items', 'q', 'stages', 'selectedStageId', 'canViewAll'));
    }
}