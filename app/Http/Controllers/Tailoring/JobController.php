<?php

namespace App\Http\Controllers\Tailoring;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Job;
use App\Models\WorkflowStage;
use Illuminate\Http\Request;
use App\Models\DressType;
use App\Models\JobBatch;
use App\Models\JobBatchItem;
use App\Models\MeasurementTemplate;
use App\Models\Tailoring\ItemMeasurementSet;
use App\Models\Tailoring\ItemMeasurementValue;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{


    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));
        $deliveredStageId = WorkflowStage::where('code', 'DELIVERED')->value('id');

        $jobs = Job::query()
            ->with(['customer', 'currentStage'])
            ->withCount([
                'batches',
                'batches as items_count' => function ($q) {
                    $q->join('job_batch_items', 'job_batches.id', '=', 'job_batch_items.job_batch_id');
                }
            ])
            ->addSelect([
                'total_qty' => JobBatchItem::query()
                    ->selectRaw('COALESCE(SUM(job_batch_items.qty),0)')
                    ->join('job_batches', 'job_batches.id', '=', 'job_batch_items.job_batch_id')
                    ->whereColumn('job_batches.job_id', 'jobs.id'),

                'completed_qty' => JobBatchItem::query()
                    ->selectRaw('COALESCE(SUM(CASE WHEN job_batch_items.completed_at IS NOT NULL THEN job_batch_items.qty ELSE 0 END),0)')
                    ->join('job_batches', 'job_batches.id', '=', 'job_batch_items.job_batch_id')
                    ->whereColumn('job_batches.job_id', 'jobs.id'),

                'delivered_qty' => JobBatchItem::query()
                    ->selectRaw('COALESCE(SUM(CASE WHEN job_batch_items.current_stage_id = ? AND job_batch_items.completed_at IS NULL THEN job_batch_items.qty ELSE 0 END),0)', [$deliveredStageId])
                    ->join('job_batches', 'job_batches.id', '=', 'job_batch_items.job_batch_id')
                    ->whereColumn('job_batches.job_id', 'jobs.id'),

                'total_amount' => JobBatchItem::query()
                    ->selectRaw('COALESCE(SUM(COALESCE(job_batch_items.line_total, (job_batch_items.qty * job_batch_items.unit_price))),0)')
                    ->join('job_batches', 'job_batches.id', '=', 'job_batch_items.job_batch_id')
                    ->whereColumn('job_batches.job_id', 'jobs.id'),
            ])
            ->when($q, function ($query) use ($q) {
                $query->where('job_no', 'like', "%{$q}%")
                    ->orWhereHas('customer', fn($c) => $c->where('full_name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('tailoring.jobs.index', compact('jobs', 'q'));
    }
    public function create()
    {
        $customers = Customer::latest()->take(200)->get(); // simple list for now
        $firstStage = WorkflowStage::where('is_active', true)->orderBy('sort_order')->first();

        return view('tailoring.jobs.create', compact('customers', 'firstStage'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'job_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $firstStage = WorkflowStage::where('is_active', true)->orderBy('sort_order')->first();

        $job = Job::create([
            'customer_id' => $data['customer_id'],
            'job_date' => $data['job_date'] ?? now()->toDateString(),
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'current_stage_id' => $firstStage?->id,
            'created_by' => auth()->id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Job created ({$job->job_no})",
                'data' => $job
            ]);
        }

        return redirect()->route('tailoring.jobs.index')->with('success', 'Job created');
    }

    public function show(Job $job)
    {
        $job->load([
            'customer',
            'currentStage',
            'batches.items.dressType',
            'batches.items.stage',
            'batches.items.measurementTemplate.fields' => function ($q) {
                $q->orderBy('sort_order');
            },
            'batches.items.measurementSets.values',
        ]);

        // ✅ Stages list (for dashboard)
        $stages = WorkflowStage::query()
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'sort_order']);

        // ✅ All items (flat)
        $allItems = $job->batches->flatMap->items;

        // ✅ Stage stats (Qty + items count per stage)
        $stageStats = $allItems
            ->whereNull('completed_at')
            ->groupBy('current_stage_id')
            ->map(function ($items) {
                return [
                    'items_count' => $items->count(),
                    'qty_sum' => (int)$items->sum('qty'),
                ];
            });

        // ✅ Total Amount (invoice base)
        $totalAmount = (float)$allItems->sum('line_total');

        // ✅ Group items by groupKey = parent_item_id ?? id
        $groups = $allItems
            ->groupBy(function ($it) {
                return $it->parent_item_id ?: $it->id;
            })
            ->map(function ($items, $groupKey) use ($stages) {

                $first = $items->first();

                // stage-wise qty inside this group
                $stageMap = $items->whereNull('completed_at')
                    ->groupBy('current_stage_id')
                    ->map(fn($rows) => (int)$rows->sum('qty'));

                // total qty in group (active)
                $totalQty = (int)$items->whereNull('completed_at')->sum('qty');

                // pick a representative item for history link
                $historyItemId = optional($items->sortByDesc('updated_at')->first())->id;

                return [
                    'group_id' => (int)$groupKey,
                    'dress_name' => $first->dressType?->name ?? 'N/A',
                    'template_name' => $first->measurementTemplate?->name ?? '-',
                    'unit_price' => (float)($first->unit_price ?? 0),
                    'total_qty' => $totalQty,
                    'line_total' => (float)$items->sum('line_total'),
                    'stage_qty' => $stageMap,
                    'history_item_id' => $historyItemId,
                    'items' => $items->sortBy('id')->values(),
                ];
            })
            ->values();

        return view('tailoring.jobs.show', compact(
            'job',
            'stages',
            'stageStats',
            'totalAmount',
            'groups'
        ));
    }




    public function createWizard()
    {
        $customers  = Customer::latest()->take(300)->get();
        $dressTypes = DressType::where('is_active', true)->orderBy('name')->get();
        $templates  = MeasurementTemplate::where('is_active', true)->with('dressType')->get();

        return view('tailoring.jobs.create_wizard', compact('customers', 'dressTypes', 'templates'));
    }

    public function storeWizard(Request $request)
    {
        $data = $request->validate([
            // job
            'customer_id' => ['required', 'exists:customers,id'],
            'job_date'    => ['required', 'date'],
            'due_date'    => ['required', 'date'],
            'notes'       => ['nullable', 'string'],

            // batches
            'batches'              => ['required', 'array', 'min:1'],
            'batches.*.batch_date' => ['required', 'date'],
            'batches.*.due_date'   => ['required', 'date'],
            'batches.*.notes'      => ['nullable', 'string'],

            // items
            'batches.*.items'                          => ['required', 'array', 'min:1'],
            'batches.*.items.*.dress_type_id'          => ['required', 'exists:dress_types,id'],
            'batches.*.items.*.measurement_template_id' => ['nullable', 'exists:measurement_templates,id'],
            'batches.*.items.*.qty'                    => ['required', 'integer', 'min:1'],
            'batches.*.items.*.per_piece_measurement'  => ['nullable', 'boolean'],
            'batches.*.items.*.notes'                  => ['nullable', 'string'],

            // ✅ price
            'batches.*.items.*.unit_price'             => ['nullable', 'numeric', 'min:0'],

            // measurements
            'batches.*.items.*.measurements' => ['nullable', 'array'],
            'batches.*.items.*.notes_map'    => ['nullable', 'array'],
        ]);

        $firstStage = WorkflowStage::where('is_active', true)->orderBy('sort_order')->first();

        $job = DB::transaction(function () use ($data, $firstStage) {

            $job = Job::create([
                'customer_id'      => $data['customer_id'],
                'job_date'         => $data['job_date'] ?? now()->toDateString(),
                'due_date'         => $data['due_date'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'current_stage_id' => $firstStage?->id,
                'created_by'       => auth()->id(),
            ]);

            foreach ($data['batches'] as $b) {

                $batchNo = JobBatch::nextBatchNo($job->id);

                $batch = JobBatch::create([
                    'job_id'     => $job->id,
                    'batch_no'   => $batchNo,
                    'batch_date' => $b['batch_date'] ?? $job->job_date,
                    'due_date'   => $b['due_date'] ?? $job->due_date,
                    'notes'      => $b['notes'] ?? null,
                ]);

                foreach ($b['items'] as $it) {

                    $item = JobBatchItem::create([
                        'job_batch_id'            => $batch->id,
                        'dress_type_id'           => $it['dress_type_id'],
                        'measurement_template_id' => $it['measurement_template_id'] ?? null,
                        'qty'                     => (int)$it['qty'],
                        'per_piece_measurement'   => (bool)($it['per_piece_measurement'] ?? false),
                        'notes'                   => $it['notes'] ?? null,
                        'current_stage_id'        => 1,

                        // ✅ price
                        'unit_price'              => (float)($it['unit_price'] ?? 0),
                    ]);

                    // If template selected => save measurements from wizard
                    if (!empty($it['measurement_template_id'])) {

                        $template = MeasurementTemplate::with(['fields' => fn($q) => $q->orderBy('sort_order')])
                            ->find($it['measurement_template_id']);

                        if (!$template) {
                            throw new \Exception("Template not found for item");
                        }

                        $measurements = $it['measurements'] ?? null;
                        $notesMap     = $it['notes_map'] ?? [];

                        if (!$measurements || !is_array($measurements)) {
                            throw new \Exception("Measurements missing for one item (template selected).");
                        }

                        $this->saveMeasurementsForItem($item, $template->fields, $measurements, $notesMap);
                    }
                }
            }

            return $job;
        });

        return response()->json([
            'success' => true,
            'message' => "Job created ({$job->job_no})",
            'data'    => ['id' => $job->id],
        ]);
    }

    public function editWizard(Job $job)
    {
        $customers = Customer::latest()->take(200)->get();

        $dressTypes = DressType::where('is_active', true)->orderBy('name')->get();
        $templates  = MeasurementTemplate::where('is_active', true)->with('dressType')->get();

        $job->load([
            'customer',
            'batches.items.dressType',
            'batches.items.measurementTemplate.fields' => fn($q) => $q->orderBy('sort_order'),
        ]);

        // ✅ Load existing measurements WITHOUT item->measurementSets relationship
        $itemIds = $job->batches->flatMap(fn($b) => $b->items)->pluck('id')->all();

        $sets = ItemMeasurementSet::query()
            ->whereIn('job_batch_item_id', $itemIds)
            ->with('values')
            ->get();

        // map: measurements[item_id][same|1..N][field_id] = value + notes
        $existingMeasurements = [];
        foreach ($sets as $set) {
            $itemId = $set->job_batch_item_id;
            $key = $set->piece_no === null ? 'same' : (string)$set->piece_no;

            $existingMeasurements[$itemId][$key]['_notes'] = $set->notes;

            foreach ($set->values as $v) {
                $existingMeasurements[$itemId][$key][$v->measurement_field_id] = $v->value;
            }
        }

        return view('tailoring.jobs.edit_wizard', compact(
            'job',
            'customers',
            'dressTypes',
            'templates',
            'existingMeasurements'
        ));
    }

    public function updateWizard(Request $request, Job $job)
    {
        $data = $request->validate([
            // job
            'customer_id' => ['required', 'exists:customers,id'],
            'job_date'    => ['required', 'date'],
            'due_date'    => ['required', 'date'],
            'notes'       => ['nullable', 'string'],

            // batches
            'batches'              => ['nullable', 'array'],
            'batches.*.id'         => ['nullable', 'integer'],
            'batches.*.batch_date' => ['required', 'date'],
            'batches.*.due_date'   => ['required', 'date'],
            'batches.*.notes'      => ['nullable', 'string'],

            // items
            'batches.*.items'                           => ['nullable', 'array'],
            'batches.*.items.*.id'                      => ['nullable', 'integer'],
            'batches.*.items.*.dress_type_id'           => ['required', 'exists:dress_types,id'],
            'batches.*.items.*.measurement_template_id' => ['nullable', 'exists:measurement_templates,id'],
            'batches.*.items.*.qty'                     => ['required', 'integer', 'min:1'],
            'batches.*.items.*.per_piece_measurement'   => ['nullable', 'boolean'],
            'batches.*.items.*.notes'                   => ['nullable', 'string'],

            // ✅ price
            'batches.*.items.*.unit_price'              => ['nullable', 'numeric', 'min:0'],

            // measurements
            'batches.*.items.*.measurements' => ['nullable', 'array'],
            'batches.*.items.*.notes_map'    => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($job, $data) {

            // ✅ Update Job
            $job->update([
                'customer_id' => $data['customer_id'],
                'job_date'    => $data['job_date'] ?? $job->job_date,
                'due_date'    => $data['due_date'] ?? $job->due_date,
                'notes'       => $data['notes'] ?? null,
            ]);

            $incomingBatches = $data['batches'] ?? [];

            $job->load('batches.items');
            $existingBatchIds = $job->batches->pluck('id')->all();
            $keepBatchIds = [];

            foreach ($incomingBatches as $b) {

                // ✅ Upsert Batch
                $batch = null;

                if (!empty($b['id']) && in_array((int)$b['id'], $existingBatchIds, true)) {
                    $batch = JobBatch::where('job_id', $job->id)->where('id', $b['id'])->first();

                    if ($batch) {
                        $batch->update([
                            'batch_date' => $b['batch_date'] ?? $batch->batch_date,
                            'due_date'   => $b['due_date'] ?? $batch->due_date,
                            'notes'      => $b['notes'] ?? null,
                        ]);
                    }
                }

                if (!$batch) {
                    $batchNo = JobBatch::nextBatchNo($job->id);
                    $batch = JobBatch::create([
                        'job_id'     => $job->id,
                        'batch_no'   => $batchNo,
                        'batch_date' => $b['batch_date'] ?? now()->toDateString(),
                        'due_date'   => $b['due_date'] ?? $job->due_date,
                        'notes'      => $b['notes'] ?? null,
                    ]);
                }

                $keepBatchIds[] = $batch->id;

                // ✅ Items
                $incomingItems = $b['items'] ?? [];
                $batch->load('items');
                $existingItemIds = $batch->items->pluck('id')->all();
                $keepItemIds = [];

                foreach ($incomingItems as $it) {

                    $perPiece = !empty($it['per_piece_measurement']) ? true : false;

                    // ✅ price
                    $unitPrice = (float)($it['unit_price'] ?? 0);

                    // upsert item
                    $item = null;

                    if (!empty($it['id']) && in_array((int)$it['id'], $existingItemIds, true)) {
                        $item = JobBatchItem::where('job_batch_id', $batch->id)
                            ->where('id', $it['id'])
                            ->first();

                        if ($item) {
                            $item->update([
                                'dress_type_id'           => $it['dress_type_id'],
                                'measurement_template_id' => $it['measurement_template_id'] ?? null,
                                'qty'                     => (int)$it['qty'],
                                'per_piece_measurement'   => $perPiece,
                                'notes'                   => $it['notes'] ?? null,
                                'current_stage_id'        => 1,

                                // ✅ price
                                'unit_price'              => $unitPrice,
                            ]);
                        }
                    }

                    if (!$item) {
                        $item = JobBatchItem::create([
                            'job_batch_id'            => $batch->id,
                            'dress_type_id'           => $it['dress_type_id'],
                            'measurement_template_id' => $it['measurement_template_id'] ?? null,
                            'qty'                     => (int)$it['qty'],
                            'per_piece_measurement'   => $perPiece,
                            'notes'                   => $it['notes'] ?? null,
                            'current_stage_id'        => 1,

                            // ✅ price
                            'unit_price'              => $unitPrice,
                        ]);
                    }

                    $keepItemIds[] = $item->id;

                    // ✅ Save measurements if template selected
                    $templateId   = $it['measurement_template_id'] ?? null;
                    $measurements = $it['measurements'] ?? [];
                    $notesMap     = $it['notes_map'] ?? [];

                    if ($templateId) {
                        $tpl = MeasurementTemplate::with(['fields' => fn($q) => $q->orderBy('sort_order')])
                            ->find($templateId);

                        if ($tpl) {
                            $fieldIds = $tpl->fields->pluck('id')->all();

                            // If qty reduced, remove old piece sets > new qty (only for per-piece mode)
                            if ($perPiece) {
                                ItemMeasurementSet::where('job_batch_item_id', $item->id)
                                    ->whereNotNull('piece_no')
                                    ->where('piece_no', '>', (int)$item->qty)
                                    ->delete();
                            }

                            if (!$perPiece) {
                                $values = $measurements['same'] ?? [];

                                $set = ItemMeasurementSet::updateOrCreate(
                                    ['job_batch_item_id' => $item->id, 'piece_no' => null],
                                    ['captured_by' => auth()->id(), 'notes' => $notesMap['same'] ?? null]
                                );

                                foreach ($fieldIds as $fid) {
                                    $val = $values[$fid] ?? null;
                                    $val = ($val === '') ? null : $val;

                                    ItemMeasurementValue::updateOrCreate(
                                        ['item_measurement_set_id' => $set->id, 'measurement_field_id' => $fid],
                                        ['value' => $val]
                                    );
                                }

                                // OPTIONAL: remove per-piece sets if switched from per-piece to same
                                ItemMeasurementSet::where('job_batch_item_id', $item->id)
                                    ->whereNotNull('piece_no')
                                    ->delete();
                            } else {
                                for ($p = 1; $p <= (int)$item->qty; $p++) {
                                    $k = (string)$p;
                                    $values = $measurements[$k] ?? [];

                                    $set = ItemMeasurementSet::updateOrCreate(
                                        ['job_batch_item_id' => $item->id, 'piece_no' => $p],
                                        ['captured_by' => auth()->id(), 'notes' => $notesMap[$k] ?? null]
                                    );

                                    foreach ($fieldIds as $fid) {
                                        $val = $values[$fid] ?? null;
                                        $val = ($val === '') ? null : $val;

                                        ItemMeasurementValue::updateOrCreate(
                                            ['item_measurement_set_id' => $set->id, 'measurement_field_id' => $fid],
                                            ['value' => $val]
                                        );
                                    }
                                }

                                // OPTIONAL: remove "same" set if switched from same to per-piece
                                ItemMeasurementSet::where('job_batch_item_id', $item->id)
                                    ->whereNull('piece_no')
                                    ->delete();
                            }
                        }
                    } else {
                        // If template removed, you may want to remove measurements.
                        // Comment this block if you want to keep old measurements in DB.
                        ItemMeasurementSet::where('job_batch_item_id', $item->id)->delete();
                    }
                }

                // delete removed items
                $toDeleteItems = array_diff($existingItemIds, $keepItemIds);
                if (!empty($toDeleteItems)) {
                    JobBatchItem::whereIn('id', $toDeleteItems)->delete();
                }
            }

            // delete removed batches
            $toDeleteBatches = array_diff($existingBatchIds, $keepBatchIds);
            if (!empty($toDeleteBatches)) {
                JobBatch::whereIn('id', $toDeleteBatches)->delete();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Job updated successfully',
            'data'    => $job->fresh(),
        ]);
    }

    private function saveMeasurementsForItem(JobBatchItem $item, $fields, array $measurements, array $notesMap = [])
    {
        $fieldIds = collect($fields)->pluck('id')->all();

        // validate required fields
        foreach ($fields as $f) {
            if (!$f->is_required) continue;

            if (!$item->per_piece_measurement) {
                $v = $measurements['same'][$f->id] ?? null;
                if ($v === null || $v === '') {
                    throw new \Exception("{$f->label} is required.");
                }
            } else {
                for ($p = 1; $p <= $item->qty; $p++) {
                    $k = (string)$p;
                    $v = $measurements[$k][$f->id] ?? null;
                    if ($v === null || $v === '') {
                        throw new \Exception("{$f->label} is required for Piece {$p}.");
                    }
                }
            }
        }

        // save
        if (!$item->per_piece_measurement) {

            $set = ItemMeasurementSet::updateOrCreate(
                ['job_batch_item_id' => $item->id, 'piece_no' => null],
                ['captured_by' => auth()->id(), 'notes' => $notesMap['same'] ?? null]
            );

            foreach ($fieldIds as $fid) {
                $val = $measurements['same'][$fid] ?? null;
                $val = ($val === '') ? null : $val;

                ItemMeasurementValue::updateOrCreate(
                    ['item_measurement_set_id' => $set->id, 'measurement_field_id' => $fid],
                    ['value' => $val]
                );
            }
        } else {

            for ($p = 1; $p <= $item->qty; $p++) {
                $k = (string)$p;

                $set = ItemMeasurementSet::updateOrCreate(
                    ['job_batch_item_id' => $item->id, 'piece_no' => $p],
                    ['captured_by' => auth()->id(), 'notes' => $notesMap[$k] ?? null]
                );

                foreach ($fieldIds as $fid) {
                    $val = $measurements[$k][$fid] ?? null;
                    $val = ($val === '') ? null : $val;

                    ItemMeasurementValue::updateOrCreate(
                        ['item_measurement_set_id' => $set->id, 'measurement_field_id' => $fid],
                        ['value' => $val]
                    );
                }
            }
        }
    }

    public function invoicePdf(Job $job)
    {
        // Load everything needed
        $job->load([
            'customer',
            'currentStage',
            'batches.items.dressType',
            'batches.items.measurementTemplate',
        ]);

        // Build lines (invoice items)
        $lines = [];

        foreach ($job->batches as $batch) {
            foreach ($batch->items as $it) {
                $qty = (int)($it->qty ?? 0);
                $unit = (float)($it->unit_price ?? 0);
                $lineTotal = (float)($it->line_total ?? ($qty * $unit));

                $lines[] = [
                    'batch_no' => $batch->batch_no,
                    'dress' => $it->dressType?->name ?? 'N/A',
                    'template' => $it->measurementTemplate?->name ?? '-',
                    'qty' => $qty,
                    'unit_price' => $unit,
                    'line_total' => $lineTotal,
                    'notes' => $it->notes,
                ];
            }
        }

        $subTotal = collect($lines)->sum('line_total');
        $discount = 0; // if you add discount later
        $grandTotal = max(0, $subTotal - $discount);

        // Invoice meta
        $invoiceNo = 'INV-' . $job->job_no; // you can change format
        $invoiceDate = now()->toDateString();

        // Company details (change to your company)
        $company = [
            'name' => config('app.name', 'Tailoring System'),
            'address' => 'Your Address Line 1, City',
            'phone' => '07X XXX XXXX',
            'email' => 'your@email.com',
        ];

        $pdf = Pdf::loadView('tailoring.jobs.invoice_pdf', [
            'company' => $company,
            'job' => $job,
            'lines' => $lines,
            'invoiceNo' => $invoiceNo,
            'invoiceDate' => $invoiceDate,
            'subTotal' => $subTotal,
            'discount' => $discount,
            'grandTotal' => $grandTotal,
        ])->setPaper('a4');

        return $pdf->stream($invoiceNo . '.pdf'); // open in browser
        // return $pdf->download($invoiceNo . '.pdf'); // if you want direct download
    }

    public function destroy(Job $job)
    {
        DB::transaction(function () use ($job) {

            foreach ($job->batches as $batch) {

                foreach ($batch->items as $item) {

                    $item->measurementSets()->delete();
                    $item->handoverLogs()->delete();
                }

                $batch->items()->delete();
            }

            $job->batches()->delete();
            $job->delete();
        });

        return redirect()->route('tailoring.jobs.index')
            ->with('success', 'Job deleted successfully.');
    }
}
