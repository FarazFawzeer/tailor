<?php

namespace App\Http\Controllers\Tailoring;

use App\Http\Controllers\Controller;
use App\Models\HandoverLog;
use App\Models\JobBatchItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WorkflowStage; // add
use Illuminate\Support\Collection;

class HandoverController extends Controller
{
    public function index(Request $request)
{
    $q = trim((string)$request->get('q'));

    // Base query (reuse for pagination + stage summary)
    $base = JobBatchItem::query()
        ->with([
            'jobBatch.job.customer',
            'dressType',
            'stage'
        ])
        ->when($q, function ($query) use ($q) {
            $query->whereHas('jobBatch.job', function ($qq) use ($q) {
                $qq->where('job_no', 'like', "%{$q}%");
            })->orWhereHas('jobBatch', function ($qq) use ($q) {
                $qq->where('batch_no', 'like', "%{$q}%");
            });
        });

    // Paginated items (same as before)
    $items = (clone $base)
        ->orderByDesc('updated_at')
        ->paginate(15)
        ->withQueryString();

    // ✅ Group only the CURRENT page items by Job No (still keeps pagination)
    $groupedJobs = $items->getCollection()->groupBy(function ($it) {
        return $it->jobBatch?->job?->job_no ?? 'UNKNOWN';
    });

    // ✅ Stage summary (for analysis)
    $stageSummary = (clone $base)
        ->selectRaw('current_stage_id, COUNT(*) as item_count, COALESCE(SUM(qty),0) as qty_sum')
        ->groupBy('current_stage_id')
        ->get()
        ->map(function ($r) {
            return [
                'stage_id' => $r->current_stage_id,
                'item_count' => (int)$r->item_count,
                'qty_sum' => (int)$r->qty_sum,
            ];
        })
        ->keyBy('stage_id');

    // Active stages list (for correct order + names)
    $stages = WorkflowStage::query()
        ->where('is_active', 1)
        ->orderBy('sort_order')
        ->get(['id','name','sort_order']);

    return view('tailoring.handover.index', compact(
        'items',
        'groupedJobs',
        'stages',
        'stageSummary',
        'q'
    ));
}

    public function create(JobBatchItem $item)
    {
        $item->load(['jobBatch.job.customer', 'dressType', 'stage']);

        // next stage = current stage sort_order + 1
        $current = $item->stage;
        $nextStage = null;

        if ($current) {
            $nextStage = DB::table('workflow_stages')
                ->where('is_active', 1)
                ->where('sort_order', '>', $current->sort_order)
                ->orderBy('sort_order')
                ->first();
        }

        // receiver list (staff users)
        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('tailoring.handover.create', compact('item', 'nextStage', 'users'));
    }

    public function store(Request $request, JobBatchItem $item)
    {
        $item->load('stage');

        if ($item->completed_at) {
            return response()->json(['success' => false, 'message' => 'This item is already completed.'], 422);
        }

        $data = $request->validate([
            'received_by' => ['required', 'exists:users,id'],
            'qty' => ['required', 'integer', 'min:1', 'max:' . max(1, (int)$item->qty)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $handoverQty = (int)$data['qty'];

        $current = $item->stage;
        if (!$current) {
            return response()->json(['success' => false, 'message' => 'Current stage not set for this item.'], 422);
        }

        // Determine next stage
        $next = DB::table('workflow_stages')
            ->where('is_active', 1)
            ->where('sort_order', '>', $current->sort_order)
            ->orderBy('sort_order')
            ->first();

        if (!$next) {
            return response()->json(['success' => false, 'message' => 'No next stage found. Use Complete instead.'], 422);
        }

        DB::transaction(function () use ($item, $current, $next, $data, $handoverQty) {

            // ✅ CASE 1: Full qty handover -> just move stage
            if ($handoverQty >= (int)$item->qty) {

                HandoverLog::create([
                    'job_batch_item_id' => $item->id,
                    'from_stage_id' => $current->id,
                    'to_stage_id' => $next->id,
                    'qty' => $handoverQty,
                    'handed_over_by' => auth()->id(),
                    'received_by' => (int)$data['received_by'],
                    'notes' => $data['notes'] ?? null,
                    'handover_at' => now(),
                ]);

                DB::table('job_batch_items')
                    ->where('id', $item->id)
                    ->update([
                        'current_stage_id' => $next->id,
                        'updated_at' => now(),
                    ]);

                return;
            }

            // ✅ CASE 2: Partial qty handover -> SPLIT
            $remainingQty = (int)$item->qty - $handoverQty;

            // 2A) Reduce current row qty (stays in current stage)
            DB::table('job_batch_items')
                ->where('id', $item->id)
                ->update([
                    'qty' => $remainingQty,
                    'updated_at' => now(),
                ]);

            // 2B) Create new row for handed over qty (moves to next stage)
            $newItemId = DB::table('job_batch_items')->insertGetId([
                'parent_item_id' => $item->parent_item_id ?? $item->id, // keep grouping
                'job_batch_id' => $item->job_batch_id,
                'dress_type_id' => $item->dress_type_id,
                'measurement_template_id' => $item->measurement_template_id,
                'qty' => $handoverQty,
                'current_stage_id' => $next->id,
                'per_piece_measurement' => $item->per_piece_measurement,
                'notes' => $item->notes,
                'unit_price' => $item->unit_price ?? 0,
                'line_total' => ($item->unit_price ?? 0) * $handoverQty,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2C) Log handover against the NEW item (because that qty moved)
            HandoverLog::create([
                'job_batch_item_id' => $newItemId,
                'from_stage_id' => $current->id,
                'to_stage_id' => $next->id,
                'qty' => $handoverQty,
                'handed_over_by' => auth()->id(),
                'received_by' => (int)$data['received_by'],
                'notes' => $data['notes'] ?? null,
                'handover_at' => now(),
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Handover saved. Partial qty split correctly.']);
    }
public function complete(Request $request, JobBatchItem $item)
{
    if ($item->completed_at) {
        return response()->json(['success' => false, 'message' => 'Already completed.'], 422);
    }

    $item->load('stage');

    if (!$item->stage) {
        return response()->json(['success' => false, 'message' => 'Current stage not found.'], 422);
    }

    if (strtoupper($item->stage->code ?? '') !== 'DELIVERED') {
        return response()->json([
            'success' => false,
            'message' => 'Item can be completed only after it reaches the Delivering stage.'
        ], 422);
    }

    $data = $request->validate([
        'notes' => ['nullable', 'string', 'max:2000'],
    ]);

    DB::transaction(function () use ($item, $data) {
        HandoverLog::create([
            'job_batch_item_id' => $item->id,
            'from_stage_id' => $item->current_stage_id,
            'to_stage_id' => null,
            'qty' => (int)$item->qty,
            'handed_over_by' => auth()->id(),
            'received_by' => null,
            'notes' => $data['notes'] ?? 'Completed',
            'handover_at' => now(),
        ]);

        $item->update([
            'completed_at' => now(),
            'completed_by' => auth()->id(),
        ]);
    });

    return response()->json(['success' => true, 'message' => 'Item marked as completed.']);
}
    public function history(JobBatchItem $item)
    {
        $item->load(['jobBatch.job.customer', 'dressType', 'stage']);

        $logs = HandoverLog::query()
            ->where('job_batch_item_id', $item->id)
            ->with(['fromStage', 'toStage', 'handedBy', 'receivedBy'])
            ->orderByDesc('handover_at')
            ->get();

        return view('tailoring.handover.history', compact('item', 'logs'));
    }


    public function createGroup(int $groupId)
    {
        // All rows in this group (root + children)
        $rows = JobBatchItem::query()
            ->where(function ($q) use ($groupId) {
                $q->where('id', $groupId)->orWhere('parent_item_id', $groupId);
            })
            ->whereNull('completed_at')
            ->with(['jobBatch.job.customer', 'dressType', 'stage'])
            ->get();

        if ($rows->isEmpty()) {
            abort(404, 'Group not found');
        }

        // Use first row for header info
        $headerItem = $rows->first();

        // Stage wise availability (sum qty per stage)
        $stageSummary = $rows->groupBy('current_stage_id')->map(function (Collection $items) {
            return [
                'stage_id' => $items->first()->current_stage_id,
                'stage_name' => $items->first()->stage?->name ?? 'N/A',
                'sort_order' => $items->first()->stage?->sort_order ?? 999,
                'qty' => $items->sum('qty'),
            ];
        })->values()->sortBy('sort_order')->values();

        // Stages list for next-stage calculation
        $stages = WorkflowStage::query()
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        // Receiver list
        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return view('tailoring.handover.group_create', compact(
            'groupId',
            'rows',
            'headerItem',
            'stageSummary',
            'stages',
            'users'
        ));
    }

    public function storeGroup(Request $request, int $groupId)
    {
        $data = $request->validate([
            'from_stage_id' => ['required', 'exists:workflow_stages,id'],
            'received_by' => ['required', 'exists:users,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $fromStage = WorkflowStage::findOrFail($data['from_stage_id']);

        // Determine next stage
        $next = WorkflowStage::query()
            ->where('is_active', 1)
            ->where('sort_order', '>', $fromStage->sort_order)
            ->orderBy('sort_order')
            ->first();

        if (!$next) {
            return response()->json(['success' => false, 'message' => 'No next stage found for selected From Stage.'], 422);
        }

        // Fetch rows ONLY in selected from stage
        $stageRows = JobBatchItem::query()
            ->where(function ($q) use ($groupId) {
                $q->where('id', $groupId)->orWhere('parent_item_id', $groupId);
            })
            ->whereNull('completed_at')
            ->where('current_stage_id', $fromStage->id)
            ->orderBy('id') // FIFO
            ->lockForUpdate()
            ->get();

        if ($stageRows->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No qty available in selected stage.'], 422);
        }

        $available = $stageRows->sum('qty');
        $moveQty = (int)$data['qty'];

        if ($moveQty > $available) {
            return response()->json([
                'success' => false,
                'message' => "Only {$available} qty available in {$fromStage->name} stage."
            ], 422);
        }

        DB::transaction(function () use ($stageRows, $fromStage, $next, $data, $moveQty, $groupId) {

            $remainingToMove = $moveQty;

            foreach ($stageRows as $row) {
                if ($remainingToMove <= 0) break;

                $take = min($remainingToMove, (int)$row->qty);

                // If moving full row qty -> just update stage
                if ($take === (int)$row->qty) {

                    // Log against same row (it moved)
                    HandoverLog::create([
                        'job_batch_item_id' => $row->id,
                        'from_stage_id' => $fromStage->id,
                        'to_stage_id' => $next->id,
                        'qty' => $take,
                        'handed_over_by' => auth()->id(),
                        'received_by' => (int)$data['received_by'],
                        'notes' => $data['notes'] ?? null,
                        'handover_at' => now(),
                    ]);

                    DB::table('job_batch_items')->where('id', $row->id)->update([
                        'current_stage_id' => $next->id,
                        'updated_at' => now(),
                    ]);
                } else {
                    // Partial from this row: reduce current, create new moved row
                    DB::table('job_batch_items')->where('id', $row->id)->update([
                        'qty' => (int)$row->qty - $take,
                        'updated_at' => now(),
                    ]);

                    $newItemId = DB::table('job_batch_items')->insertGetId([
                        'parent_item_id' => $row->parent_item_id ?: $row->id, // group
                        'job_batch_id' => $row->job_batch_id,
                        'dress_type_id' => $row->dress_type_id,
                        'measurement_template_id' => $row->measurement_template_id,
                        'qty' => $take,
                        'current_stage_id' => $next->id,
                        'per_piece_measurement' => $row->per_piece_measurement,
                        'notes' => $row->notes,
                        'unit_price' => $row->unit_price ?? 0,
                        'line_total' => ($row->unit_price ?? 0) * $take,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    HandoverLog::create([
                        'job_batch_item_id' => $newItemId,
                        'from_stage_id' => $fromStage->id,
                        'to_stage_id' => $next->id,
                        'qty' => $take,
                        'handed_over_by' => auth()->id(),
                        'received_by' => (int)$data['received_by'],
                        'notes' => $data['notes'] ?? null,
                        'handover_at' => now(),
                    ]);
                }

                $remainingToMove -= $take;
            }
        });

        return response()->json(['success' => true, 'message' => "Handover saved. Moved {$moveQty} from {$fromStage->name} → {$next->name}."]);
    }
}
