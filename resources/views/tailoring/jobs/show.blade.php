@extends('layouts.vertical', ['subtitle' => 'Job View (Easy)'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Tailoring Jobs', 'subtitle' => 'View (Easy Screen)'])

@php
    $highlightMap = [
        'chest' => 'zone-chest',
        'shoulder' => 'zone-shoulder',
        'sleeve_length' => 'zone-sleeve',
        'shirt_length' => 'zone-length',
        'neck' => 'zone-neck',
        'waist' => 'zone-waist',
        'hip' => 'zone-hip',
        'trouser_length' => 'zone-length',
        'bottom' => 'zone-bottom',
    ];

    $defaultFront = asset('/images/diagrams/default-front.png');
    $defaultBack  = asset('/images/diagrams/default-back.png');

    $companyNameJs = config('app.name', 'Tailoring Shop');
@endphp

<style>
    .pill { padding: 2px 10px; border-radius: 999px; font-size: 12px; background: rgba(13,110,253,.08); }
    .muted-help { font-size: 12px; color: #6c757d; }
    .info-card { border: 1px solid rgba(0,0,0,.08); border-radius: 12px; }
    .info-card .card-header { background: rgba(0,0,0,.03); border-radius: 12px 12px 0 0; }
    .kv { display:flex; gap:8px; align-items:flex-start; }
    .kv b { min-width: 110px; display:inline-block; }
    .mini { font-size: 12px; }

    .stage-chip { border:1px solid rgba(0,0,0,.08); border-radius:12px; padding:10px 12px; background:#fff; }
    .stage-chip .name { font-size: 12px; color:#6c757d; }
    .stage-chip .qty { font-size: 22px; font-weight: 700; line-height: 1; }
    .stage-chip .meta { font-size: 12px; color:#6c757d; }

    .group-card { border:1px solid rgba(0,0,0,.08); border-radius: 12px; overflow:hidden; }
    .group-head { background: rgba(0,0,0,.03); padding: 12px 14px; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; }
    .group-body { padding: 14px; }

    .stage-pill { display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:999px; background: rgba(33,37,41,.06); font-size: 12px; }
    .stage-pill b { font-size: 12px; }

    .cutter-pill { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; background: rgba(25,135,84,.10); color:#198754; font-size: 12px; border: 1px solid rgba(25,135,84,.18); }
    .cutter-pill.unassigned { background: rgba(220,53,69,.08); color:#dc3545; border-color: rgba(220,53,69,.18); }

    .price-box { border:1px dashed rgba(0,0,0,.15); border-radius: 12px; padding: 12px 14px; background:#fff; }
    .price-box .label { color:#6c757d; font-size: 12px; }
    .price-box .val { font-size: 22px; font-weight: 800; }

    /* Diagram styles */
    .diagram-wrap { position: relative; width: 100%; }
    .diagram-wrap img { width: 100%; height: auto; display: block; }
    .zone {
        position: absolute;
        border-radius: 10px;
        opacity: 0;
        transition: opacity .15s ease-in-out;
        outline: 2px dashed rgba(0,0,0,.35);
        background: rgba(255, 193, 7, 0.25);
        pointer-events: none;
    }
    .zone.active { opacity: 1; }

    .zone-neck { top: 6%; left: 38%; width: 24%; height: 10%; }
    .zone-shoulder { top: 12%; left: 20%; width: 60%; height: 12%; }
    .zone-chest { top: 25%; left: 25%; width: 50%; height: 16%; }
    .zone-sleeve { top: 20%; left: 5%; width: 20%; height: 22%; }
    .zone-waist { top: 42%; left: 28%; width: 44%; height: 14%; }
    .zone-hip { top: 55%; left: 28%; width: 44%; height: 14%; }
    .zone-length { top: 68%; left: 32%; width: 36%; height: 24%; }
    .zone-bottom { top: 86%; left: 32%; width: 36%; height: 10%; }

    .modal-diagram-card { position: sticky; top: 10px; }
</style>

{{-- TOP SUMMARY --}}
<div class="card info-card mb-3">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
            <div>
                <h5 class="card-title mb-1">Job: {{ $job->job_no }}</h5>
           <div class="muted-help">Create → Measurements → Handover stages → Delivered → Complete → Invoice</div>
            </div>
            <div class="d-flex gap-2 align-items-center">
   
            <a href="{{ route('tailoring.jobs.index') }}" class="btn btn-outline-secondary btn-sm" style="width: 100px;">Back</a>
      
                <a href="{{ route('tailoring.jobs.editWizard', $job) }}" class="btn btn-outline-primary btn-sm" style="width: 100px;">Edit </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="row g-3 align-items-start">
            <div class="col-lg-8">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="kv">
                            <b>Customer:</b>
                            <div>
                                <div class="fw-semibold">{{ $job->customer?->full_name ?? '-' }}</div>
                                <div class="text-muted mini">
                                    Phone: {{ $job->customer?->phone ?? '-' }}
                                    @if(!empty($job->customer?->email))
                                        | Email: {{ $job->customer?->email }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 text-md-end">
                        <span class="stage-pill">
                            Current Stage:
                            <b>{{ $job->currentStage?->name ?? '-' }}</b>
                        </span>
                    </div>

                    <div class="col-md-3">
                        <div class="kv"><b>Job Date:</b> <span>{{ $job->job_date?->format('d M Y') ?? '-' }}</span></div>
                    </div>
                    <div class="col-md-3">
                        <div class="kv"><b>Due Date:</b> <span>{{ $job->due_date?->format('d M Y') ?? '-' }}</span></div>
                    </div>
                    <div class="col-md-3">
                        <div class="kv"><b>Discount:</b> <span>{{ number_format((float)($job->discount ?? 0), 2) }}</span></div>
                    </div>
                    <div class="col-md-">
                        <div class="kv"><b>Notes:</b> <span>{{ $job->notes ?? '-' }}</span></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="price-box">
                    <div class="label">Total Job Amount</div>
                    <div class="val">{{ number_format((float)$totalAmount, 2) }}</div>
                    <div class="muted-help">Based on Qty × Unit Price</div>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <a class="btn btn-success btn-sm text-white"
                           href="{{ route('tailoring.jobs.invoicePdf', $job) }}"
                           target="_blank">
                           Generate Invoice
                        </a>

                        <button type="button" class="btn btn-outline-success btn-sm" id="btnPrintBill">
                            <iconify-icon icon="solar:printer-linear"></iconify-icon> Print Bill (80mm)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        
    </div>
</div>

{{-- STAGE DASHBOARD (5 in same row) --}}
<div class="card info-card mb-3">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Stage Summary</h5>
                <div class="muted-help">Total qty & items in each stage</div>
            </div>
     <span class="pill">{{ $stages->count() }} Stages</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-2 flex-nowrap" style="overflow-x:auto;">
            @foreach($stages as $s)
                @php
                    $stat = $stageStats[$s->id] ?? ['items_count'=>0,'qty_sum'=>0];
                @endphp
                <div class="col" style="min-width:190px;">
                    <div class="stage-chip">
                        <div class="d-flex justify-content-between">
                            <div class="name">{{ $s->name }}</div>
                            <span class="badge bg-light text-dark">Stage {{ $s->sort_order }}</span>
                        </div>
                        <div class="qty mt-2">{{ (int)$stat['qty_sum'] }}</div>
                        <div class="meta">{{ (int)$stat['items_count'] }} items</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- HANDOVER CENTER (GROUPED, CLEAR) --}}
<div class="card info-card mb-3">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <div>
                <h5 class="card-title mb-0">Handover & Tracking</h5>
                <div class="muted-help">Items are grouped to avoid duplicates (easy for users)</div>
            </div>
            <span class="pill">Move Qty to next stage</span>
        </div>
    </div>

    <div class="card-body">
        @forelse($groups as $g)
            @php
                // ✅ distinct cutters assigned within this group
                $groupCutterNames = collect($g['items'])
                    ->map(fn($row) => $row->assignedCutter?->name)
                    ->filter()
                    ->unique()
                    ->values();
            @endphp

            <div class="group-card mb-3">
                <div class="group-head">
                    <div>
                        <div class="fw-semibold">
                           {{ $g['dress_name'] }}
                            <span class="text-muted mini">| Total Qty: <b>{{ $g['total_qty'] }}</b></span>
                        </div>
                        <div class="text-muted mini">
                            Template: {{ $g['template_name'] }}
                            | Unit Price: {{ number_format((float)$g['unit_price'], 2) }}
                            | Total: <b>{{ number_format((float)$g['line_total'], 2) }}</b>
                        </div>

                        {{-- ✅ Cutter(s) for this group --}}
                        <div class="mt-2">
                            @if($groupCutterNames->isNotEmpty())
                                <span class="cutter-pill">
                                    <iconify-icon icon="solar:scissors-linear"></iconify-icon>
                                    Cutter(s): {{ $groupCutterNames->implode(', ') }}
                                </span>
                            @else
                                <span class="cutter-pill unassigned">
                                    <iconify-icon icon="solar:scissors-linear"></iconify-icon>
                                    No Cutter Assigned
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('tailoring.handover.group.create', $g['group_id']) }}" class="btn btn-primary btn-sm" style="width: 150px">
                            Group Handover
                        </a>

                        @if(!empty($g['history_item_id']))
                            <a href="{{ route('tailoring.handover.history', $g['history_item_id']) }}" class="btn btn-outline-dark btn-sm" style="width: 150px">
                                History
                            </a>
                        @endif
                    </div>
                </div>

                <div class="group-body">
                    {{-- Stage-wise qty chips --}}
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        @foreach($stages as $s)
                            @php $sq = (int)($g['stage_qty'][$s->id] ?? 0); @endphp
                            <span class="stage-pill">
                                {{ $s->name }}:
                                <b>{{ $sq }}</b>
                            </span>
                        @endforeach
                    </div>

                    {{-- Items in this group (for single handover) --}}
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th >Batch</th>
                                    <th >Qty</th>
                                    <th >Stage</th>
                                    <th style="min-width:150px;">Cutter</th>
                                    <th>Notes</th>
                                    <th style="width:330px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($g['items'] as $row)
                                    @php
                                        // ✅ Build the print payload for this row (server-side, always accurate)
                                        $printPayloadRow = null;

                                        if ($row->measurementTemplate) {
                                            $piecesForPrint = [];
                                            $fieldsList = $row->measurementTemplate->fields ?? collect();
                                            $setsForRow = $row->measurementSets ?? collect();

                                            foreach ($setsForRow as $set) {
                                                $pieceTitle = $set->piece_no === null ? 'All Pieces (Same)' : "Piece {$set->piece_no}";
                                                $fieldRows = [];
                                                $valuesByFieldId = $set->values->keyBy('measurement_field_id');

                                                foreach ($fieldsList as $f) {
                                                    $v = $valuesByFieldId->get($f->id);
                                                    $fieldRows[] = [
                                                        'label' => $f->label,
                                                        'unit' => $f->unit,
                                                        'value' => $v?->value,
                                                    ];
                                                }

                                                $piecesForPrint[] = [
                                                    'title' => $pieceTitle,
                                                    'fields' => $fieldRows,
                                                    'notes' => $set->notes,
                                                ];
                                            }

                                            $printPayloadRow = [
                                                'job_no' => $job->job_no,
                                                'batch_no' => $row->jobBatch?->batch_no,
                                                'dress_name' => $row->dressType?->name,
                                                'template_name' => $row->measurementTemplate?->name,
                                                'qty' => $row->qty,
                                                'per_piece' => (bool)$row->per_piece_measurement,
                                                'pieces' => $piecesForPrint,
                                                'item_notes' => $row->notes,
                                            ];
                                        }
                                    @endphp

                                    <tr>
                                        <td>{{ $row->jobBatch?->batch_no ?? '-' }}</td>
                                        <td class="fw-bold">{{ $row->qty }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $row->stage?->name ?? 'N/A' }}</span>
                                            @if($row->parent_item_id)
                                                <span class="badge bg-warning ms-1">Part</span>
                                            @endif
                                            @if($row->completed_at)
                                                <span class="badge bg-success ms-1">Completed</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($row->assignedCutter)
                                                <span class="badge bg-secondary">
                                                    <iconify-icon icon="solar:scissors-linear"></iconify-icon>
                                                    {{ $row->assignedCutter->name }}
                                                </span>
                                            @else
                                                <span class="badge bg-danger">Not Assigned</span>
                                            @endif
                                        </td>
                                        <td class="text-muted">{{ $row->notes ?? '-' }}</td>
                                        <td>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <a class="btn btn-outline-primary btn-sm w-100 {{ $row->completed_at ? 'disabled' : '' }}"
                                                   href="{{ $row->completed_at ? '#' : route('tailoring.handover.create', $row) }}" style="width: 150px;">
                                                    Single Handover
                                                </a>

                                                <a class="btn btn-outline-dark btn-sm w-100"
                                                   href="{{ route('tailoring.measurements.edit', [$job, $row->jobBatch, $row]) }}"  style="width: 150px;">
                                                    Measurements
                                                </a>

                                                @if($printPayloadRow)
                                                    <button type="button"
                                                        class="btn btn-outline-success btn-sm w-100 btnPrintReceipt"
                                                        data-print-payload='@json($printPayloadRow)'
                                                        style="width: 150px;">
                                                        <iconify-icon icon="solar:printer-linear"></iconify-icon> Print
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4">No items found.</div>
        @endforelse
    </div>
</div>

{{-- OPTIONAL: YOUR OLD BATCHES VIEW (KEEP FOR REFERENCE) --}}
<div class="card info-card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Batches & Items (Reference)</h5>
                <div class="muted-help">This is your original view section (kept for checking)</div>
            </div>
            <span class="pill">View</span>
        </div>
    </div>

    <div class="card-body">
        @forelse($job->batches as $batch)
            <div class="border rounded-3 p-3 mb-3">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                    <div>
                        <div class="fw-semibold">{{ $batch->batch_no }}</div>
                        <div class="text-muted mini">
                            Batch Date: {{ $batch->batch_date?->format('d M Y') ?? '-' }}
                            | Due: {{ $batch->due_date?->format('d M Y') ?? '-' }}
                            @if($batch->notes) | Notes: {{ $batch->notes }} @endif
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width:180px;">Dress</th>
                                <th style="min-width:220px;">Template</th>
                                <th >Qty</th>
                                <th >Mode</th>
                                <th style="min-width:150px;">Cutter</th>
                                <th >Unit Price</th>
                                <th >Line Total</th>
                                <th >Item Notes</th>
                                <th style="width:300px;">Measurements</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batch->items as $it)
                                @php
                                    $frontImg = $it->dressType?->diagram_front ? asset($it->dressType->diagram_front) : $defaultFront;
                                    $backImg  = $it->dressType?->diagram_back  ? asset($it->dressType->diagram_back)  : $defaultBack;

                                    $payload = null;

                                    $hasTemplate = !empty($it->measurementTemplate);
                                    $hasFields = $hasTemplate && !empty($it->measurementTemplate?->fields);

                                    $sets = null;
                                    try { $sets = $it->measurementSets ?? null; } catch (\Throwable $e) { $sets = null; }

                                    if ($hasFields && $sets) {
                                        $fields = $it->measurementTemplate->fields;
                                        $existing = [];
                                        foreach ($sets as $set) {
                                            $key = $set->piece_no === null ? 'same' : (string)$set->piece_no;
                                            $existing[$key] = ['_notes' => $set->notes];
                                            foreach ($set->values as $v) {
                                                $existing[$key][(int)$v->measurement_field_id] = $v->value;
                                            }
                                        }

                                        $payload = [
                                            'job_no' => $job->job_no,
                                            'batch_no' => $batch->batch_no,
                                            'dress_name' => $it->dressType?->name,
                                            'template_name' => $it->measurementTemplate?->name,
                                            'qty' => (int)$it->qty,
                                            'per_piece' => (bool)$it->per_piece_measurement,
                                            'front_img' => $frontImg,
                                            'back_img' => $backImg,
                                            'fields' => $fields->map(function($f) {
                                                return [
                                                    'id' => (int)$f->id,
                                                    'label' => $f->label,
                                                    'key' => $f->key,
                                                    'unit' => $f->unit,
                                                    'input_type' => $f->input_type,
                                                    'is_required' => (bool)$f->is_required,
                                                ];
                                            })->values()->all(),
                                            'existing' => $existing,
                                        ];
                                    }
                                @endphp

                                <tr>
                                    <td>{{ $it->dressType?->name ?? '-' }}</td>
                                    <td>{{ $it->measurementTemplate?->name ?? '-' }}</td>
                                    <td>{{ $it->qty }}</td>
                                    <td>
                                        @if($it->per_piece_measurement)
                                            <span class="badge bg-warning">Per Piece</span>
                                        @else
                                            <span class="badge bg-success">Same</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($it->assignedCutter)
                                            <span class="badge bg-secondary">
                                                <iconify-icon icon="solar:scissors-linear"></iconify-icon>
                                                {{ $it->assignedCutter->name }}
                                            </span>
                                        @else
                                            <span class="badge bg-danger">Not Assigned</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format((float)$it->unit_price, 2) }}</td>
                                    <td class="fw-bold">{{ number_format((float)$it->line_total, 2) }}</td>
                                    <td>{{ $it->notes ?? '-' }}</td>
                                    <td>
                                        <div class="d-grid gap-2">
                                            @if($payload)
                                                <button type="button"
                                                    class="btn btn-info btn-sm btnViewMeasurements"
                                                    data-payload='@json($payload)'>
                                                    View in Screen
                                                </button>
                                            @endif

                                            <a class="btn btn-outline-dark btn-sm"
                                                href="{{ route('tailoring.measurements.edit', [$job, $batch, $it]) }}">
                                                Open Measurements 
                                            </a>

                                            @if($payload)
                                                <button type="button"
                                                    class="btn btn-outline-success btn-sm btnPrintReceiptFromView"
                                                    data-payload='@json($payload)'>
                                                    <iconify-icon icon="solar:printer-linear"></iconify-icon> Print
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No items in this batch.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        @empty
            <div class="text-center text-muted py-4">No batches yet.</div>
        @endforelse
    </div>
</div>

{{-- MEASUREMENTS VIEW MODAL (READ ONLY) --}}
<div class="modal fade" id="viewMeasurementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0">Measurements (View Only)</h5>
                    <div class="muted-help" id="vmSubtitle"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div id="vmBody"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    const HIGHLIGHT_MAP = @json($highlightMap);
    const COMPANY_NAME = @json($companyNameJs);

    const vmModalEl = document.getElementById('viewMeasurementModal');
    const vmBody = document.getElementById('vmBody');
    const vmSubtitle = document.getElementById('vmSubtitle');

    function zoneForKey(key) {
        if (!key) return '';
        return HIGHLIGHT_MAP[key] || '';
    }

    function clearZones(container) {
        container.querySelectorAll('.zone').forEach(z => z.classList.remove('active'));
    }

    function enableDiagramSwitching(container, frontImg, backImg) {
        const diagramImage = container.querySelector('#diagramImage');
        const btnFront = container.querySelector('#btnFront');
        const btnBack  = container.querySelector('#btnBack');

        btnFront?.addEventListener('click', () => {
            diagramImage.src = frontImg;
            btnFront.classList.add('active');
            btnBack.classList.remove('active');
            clearZones(container);
        });

        btnBack?.addEventListener('click', () => {
            diagramImage.src = backImg;
            btnBack.classList.add('active');
            btnFront.classList.remove('active');
            clearZones(container);
        });
    }

    function enableFieldHighlighting(container) {
        container.addEventListener('mouseover', function(e) {
            if (!e.target.classList.contains('measure-field')) return;
            const zone = e.target.dataset.zone;
            clearZones(container);
            if (!zone) return;
            const el = container.querySelector('.' + zone);
            if (el) el.classList.add('active');
        });

        container.addEventListener('focusin', function(e) {
            if (!e.target.classList.contains('measure-field')) return;
            const zone = e.target.dataset.zone;
            clearZones(container);
            if (!zone) return;
            const el = container.querySelector('.' + zone);
            if (el) el.classList.add('active');
        });

        container.addEventListener('focusout', function(e) {
            if (!e.target.classList.contains('measure-field')) return;
            setTimeout(() => clearZones(container), 120);
        });
    }

    function buildReadOnlyInputs(fields, existingMap, prefixTitle) {
        const rows = fields.map(f => {
            const val = (existingMap && (String(f.id) in existingMap)) ? (existingMap[String(f.id)] ?? '') : '';
            const zone = zoneForKey(f.key);
            return `
                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        ${f.label} <small class="text-muted">(${f.unit})</small>
                    </label>
                    <input type="text"
                        class="form-control measure-field"
                        data-zone="${zone}"
                        value="${(val ?? '').toString().replaceAll('"','&quot;')}"
                        readonly>
                </div>
            `;
        }).join('');

        const notes = (existingMap && existingMap._notes) ? existingMap._notes : '';

        return `
            <div class="card border mb-3">
                <div class="card-header"><b>${prefixTitle}</b></div>
                <div class="card-body">
                    <div class="row">${rows}</div>
                    <div class="mb-0">
                        <label class="form-label">Notes</label>
                        <input class="form-control" value="${(notes ?? '').toString().replaceAll('"','&quot;')}" readonly>
                    </div>
                </div>
            </div>
        `;
    }

    function buildModalHtml(payload) {
        const frontImg = payload.front_img;
        const backImg  = payload.back_img;

        const fields = payload.fields || [];
        const existing = payload.existing || {};

        let formHtml = '';

        if (!payload.per_piece) {
            formHtml += buildReadOnlyInputs(fields, existing.same || existing['same'], `Same measurements for all pieces (Qty ${payload.qty})`);
        } else {
            let tabs = '';
            let panes = '';

            for (let p = 1; p <= payload.qty; p++) {
                tabs += `
                    <li class="nav-item" role="presentation">
                        <button class="nav-link ${p===1?'active':''}"
                            data-bs-toggle="tab"
                            data-bs-target="#pane-${p}"
                            type="button" role="tab">
                            Piece ${p}
                        </button>
                    </li>
                `;

                const pieceMap = existing[String(p)] || {};
                panes += `
                    <div class="tab-pane fade ${p===1?'show active':''}" id="pane-${p}" role="tabpanel">
                        ${buildReadOnlyInputs(fields, pieceMap, `Piece ${p} Measurements`)}
                    </div>
                `;
            }

            formHtml = `
                <ul class="nav nav-tabs mb-3" style="overflow-x:auto; flex-wrap:nowrap;">
                    ${tabs}
                </ul>
                <div class="tab-content">${panes}</div>
            `;
        }

        return `
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="card border modal-diagram-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <b>Diagram</b>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-dark active" id="btnFront">Front</button>
                                <button type="button" class="btn btn-outline-dark" id="btnBack">Back</button>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="diagram-wrap">
                                <img id="diagramImage" src="${frontImg}" class="img-fluid rounded" alt="Diagram">

                                <div class="zone zone-neck"></div>
                                <div class="zone zone-shoulder"></div>
                                <div class="zone zone-chest"></div>
                                <div class="zone zone-sleeve"></div>
                                <div class="zone zone-waist"></div>
                                <div class="zone zone-hip"></div>
                                <div class="zone zone-length"></div>
                                <div class="zone zone-bottom"></div>
                            </div>

                            <div class="mt-2 text-muted small">
                                Hover / focus a measurement field to highlight area.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    ${formHtml}
                </div>
            </div>
        `;
    }

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btnViewMeasurements');
        if (!btn) return;

        const payload = JSON.parse(btn.dataset.payload || '{}');
        vmSubtitle.textContent = `${payload.dress_name || ''} | Template: ${payload.template_name || ''} | Qty ${payload.qty || ''} | ${payload.per_piece ? 'Per Piece' : 'Same for All'}`;

        vmBody.innerHTML = buildModalHtml(payload);

        enableDiagramSwitching(vmBody, payload.front_img, payload.back_img);
        enableFieldHighlighting(vmBody);

        const modal = new bootstrap.Modal(vmModalEl);
        modal.show();
    });

    // =====================================================================
    // SHARED HELPERS
    // =====================================================================

    function escapeHtml(str) {
        return String(str ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function formatMoney(n) {
        const x = Number(n || 0);
        return x.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // =====================================================================
    // MEASUREMENT RECEIPT PRINTING (80mm thermal printer friendly)
    // One slip per item, showing measurement values.
    // =====================================================================

    function printMeasurementReceipt(payload) {
        const piecesHtml = (payload.pieces || []).map(piece => {
            const rows = (piece.fields || []).map(f => `
                <tr>
                    <td class="f-label">${escapeHtml(f.label)}${f.unit ? ` <span class="f-unit">(${escapeHtml(f.unit)})</span>` : ''}</td>
                    <td class="f-value">${f.value !== null && f.value !== '' && f.value !== undefined ? escapeHtml(String(f.value)) : '-'}</td>
                </tr>
            `).join('');

            return `
                <div class="piece-block">
                    <div class="piece-title">${escapeHtml(piece.title)}</div>
                    <table class="meas-table">
                        ${rows}
                    </table>
                    ${piece.notes ? `<div class="piece-notes">Note: ${escapeHtml(piece.notes)}</div>` : ''}
                </div>
            `;
        }).join('<div class="divider-dashed"></div>');

        const now = new Date();
        const printedAt = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();

        const html = `
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Measurement Receipt</title>
<style>
    @page { size: 80mm auto; margin: 0; }
    * { box-sizing: border-box; }
    body {
        width: 80mm;
        margin: 0 auto;
        padding: 4mm 4mm 8mm 4mm;
        font-family: 'Courier New', Consolas, monospace;
        font-size: 12px;
        color: #000;
        background: #fff;
    }
    .center { text-align: center; }
    .bold { font-weight: 700; }
    .shop-name { font-size: 16px; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 2px; }
    .shop-sub { font-size: 10px; color: #333; margin-bottom: 6px; }
    .divider-solid { border-top: 1px solid #000; margin: 6px 0; }
    .divider-dashed { border-top: 1px dashed #000; margin: 8px 0; }
    .meta-table { width: 100%; font-size: 12px; margin-bottom: 4px; }
    .meta-table td { padding: 1px 0; vertical-align: top; }
    .meta-label { width: 38%; font-weight: 700; white-space: nowrap; }
    .meta-value { width: 62%; }
    .section-title {
        font-size: 13px; font-weight: 800; text-transform: uppercase;
        letter-spacing: 0.5px; margin: 6px 0 4px 0; text-align: center;
        background: #000; color: #fff; padding: 3px 0;
    }
    .piece-block { margin-bottom: 4px; }
    .piece-title { font-weight: 700; font-size: 12.5px; margin-bottom: 3px; text-decoration: underline; }
    .meas-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .meas-table tr { border-bottom: 1px dotted #999; }
    .meas-table td { padding: 3px 0; }
    .f-label { width: 65%; }
    .f-unit { font-size: 10px; color: #444; }
    .f-value { width: 35%; text-align: right; font-weight: 800; font-size: 13px; }
    .piece-notes { font-size: 10.5px; font-style: italic; margin-top: 2px; color: #222; }
    .item-notes-box { font-size: 11px; margin-top: 6px; padding: 4px; border: 1px dashed #000; }
    .footer { margin-top: 10px; font-size: 9.5px; color: #333; text-align: center; }
    .qty-badge { display: inline-block; border: 1px solid #000; padding: 1px 8px; border-radius: 10px; font-size: 11px; font-weight: 700; }
</style>
</head>
<body>

    <div class="center shop-name">${escapeHtml(COMPANY_NAME)}</div>
    <div class="center shop-sub">Measurement Slip</div>

    <div class="divider-solid"></div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Job No</td>
            <td class="meta-value bold">${escapeHtml(payload.job_no || '-')}</td>
        </tr>
        <tr>
            <td class="meta-label">Batch No</td>
            <td class="meta-value">${escapeHtml(payload.batch_no || '-')}</td>
        </tr>
        <tr>
            <td class="meta-label">Dress</td>
            <td class="meta-value bold">${escapeHtml(payload.dress_name || '-')}</td>
        </tr>
        <tr>
            <td class="meta-label">Template</td>
            <td class="meta-value">${escapeHtml(payload.template_name || '-')}</td>
        </tr>
        <tr>
            <td class="meta-label">Qty</td>
            <td class="meta-value"><span class="qty-badge">${escapeHtml(String(payload.qty ?? '-'))}</span></td>
        </tr>
    </table>

    <div class="section-title">Measurements</div>

    ${piecesHtml || '<div class="center" style="padding:10px 0;">No measurement data</div>'}

    ${payload.item_notes ? `<div class="item-notes-box"><b>Item Notes:</b> ${escapeHtml(payload.item_notes)}</div>` : ''}

    <div class="divider-dashed"></div>

    <div class="footer">
        Printed: ${escapeHtml(printedAt)}
    </div>

</body>
</html>
        `;

        const printWin = window.open('', '_blank', 'width=400,height=600');
        if (!printWin) {
            alert('Please allow popups to print the receipt.');
            return;
        }

        printWin.document.open();
        printWin.document.write(html);
        printWin.document.close();

        printWin.onload = function () {
            printWin.focus();
            printWin.print();
        };
    }

    // Adapts the "view in screen" payload shape (fields as array of {id,label,unit,...}
    // plus an "existing" map keyed by piece) into the {pieces:[{title,fields,notes}]}
    // shape that printMeasurementReceipt expects.
    function adaptViewPayloadToPrintPayload(payload) {
        const pieces = [];

        if (!payload.per_piece) {
            const map = payload.existing?.same || {};
            const fields = (payload.fields || []).map(f => ({
                label: f.label,
                unit: f.unit,
                value: map[String(f.id)] ?? null
            }));
            pieces.push({ title: 'All Pieces (Same)', fields, notes: map._notes || '' });
        } else {
            for (let p = 1; p <= payload.qty; p++) {
                const map = payload.existing?.[String(p)] || {};
                const fields = (payload.fields || []).map(f => ({
                    label: f.label,
                    unit: f.unit,
                    value: map[String(f.id)] ?? null
                }));
                pieces.push({ title: `Piece ${p}`, fields, notes: map._notes || '' });
            }
        }

        return {
            job_no: payload.job_no,
            batch_no: payload.batch_no,
            dress_name: payload.dress_name,
            template_name: payload.template_name,
            qty: payload.qty,
            per_piece: payload.per_piece,
            pieces,
            item_notes: ''
        };
    }

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btnPrintReceipt');
        if (btn) {
            const payload = JSON.parse(btn.dataset.printPayload || '{}');
            printMeasurementReceipt(payload);
            return;
        }

        const btn2 = e.target.closest('.btnPrintReceiptFromView');
        if (btn2) {
            const rawPayload = JSON.parse(btn2.dataset.payload || '{}');
            printMeasurementReceipt(adaptViewPayloadToPrintPayload(rawPayload));
            return;
        }
    });

    // =====================================================================
    // CUSTOMER BILL / RECEIPT PRINTING (80mm thermal printer friendly)
    // ONE bill per job: job no, batch no, customer name/phone, dress type,
    // qty, unit price, total price, discount, invoice no, date.
    // =====================================================================

    function printJobBill(bill) {
        // bill shape (matches JobController::buildBillPayload()):
        // {
        //   invoice_no, invoice_date, job_no,
        //   customer_name, customer_phone,
        //   lines: [{ batch_no, dress, qty, unit_price, line_total }, ...],
        //   sub_total, discount, grand_total
        // }

        const rowsHtml = (bill.lines || []).map(ln => `
            <tr>
                <td class="b-batch">${escapeHtml(ln.batch_no || '-')}</td>
                <td class="b-dress">${escapeHtml(ln.dress || '-')}</td>
                <td class="b-qty">${escapeHtml(String(ln.qty ?? 0))}</td>
                <td class="b-price">${formatMoney(ln.unit_price)}</td>
                <td class="b-total">${formatMoney(ln.line_total)}</td>
            </tr>
        `).join('');

        const html = `
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Bill ${escapeHtml(bill.invoice_no || '')}</title>
<style>
    @page { size: 80mm auto; margin: 0; }
    * { box-sizing: border-box; }
    body {
        width: 80mm;
        margin: 0 auto;
        padding: 4mm 4mm 8mm 4mm;
        font-family: 'Courier New', Consolas, monospace;
        font-size: 12px;
        color: #000;
        background: #fff;
    }
    .center { text-align: center; }
    .bold { font-weight: 700; }
    .shop-name { font-size: 16px; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 2px; }
    .shop-sub { font-size: 10px; color: #333; margin-bottom: 6px; }
    .divider-solid { border-top: 1px solid #000; margin: 6px 0; }
    .divider-dashed { border-top: 1px dashed #000; margin: 8px 0; }
    .meta-table { width: 100%; font-size: 11.5px; margin-bottom: 4px; }
    .meta-table td { padding: 1px 0; vertical-align: top; }
    .meta-label { width: 40%; font-weight: 700; white-space: nowrap; }
    .meta-value { width: 60%; }
    .items-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 6px; }
    .items-table th {
        border-bottom: 1px solid #000;
        text-align: left;
        padding: 3px 2px;
        font-size: 10.5px;
    }
    .items-table td {
        border-bottom: 1px dotted #999;
        padding: 4px 2px;
        vertical-align: top;
    }
    .b-qty { text-align: center; width: 10%; }
    .b-price, .b-total { text-align: right; width: 22%; }
    .b-dress { width: 36%; }
    .b-batch { width: 20%; font-size: 10px; color: #444; }
    .totals-table { width: 100%; margin-top: 6px; font-size: 12px; }
    .totals-table td { padding: 2px 0; }
    .totals-table .t-label { text-align: right; color: #333; width: 60%; }
    .totals-table .t-value { text-align: right; font-weight: 700; width: 40%; }
    .grand-row .t-label, .grand-row .t-value { font-size: 14px; font-weight: 800; }
    .footer { margin-top: 12px; font-size: 9.5px; color: #333; text-align: center; }
    .thanks { margin-top: 8px; text-align: center; font-size: 12px; font-weight: 700; }
</style>
</head>
<body>

    <div class="center shop-name">${escapeHtml(COMPANY_NAME)}</div>
    <div class="center shop-sub">Customer Bill / Receipt</div>

    <div class="divider-solid"></div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Invoice No</td>
            <td class="meta-value bold">${escapeHtml(bill.invoice_no || '-')}</td>
        </tr>
        <tr>
            <td class="meta-label">Date</td>
            <td class="meta-value">${escapeHtml(bill.invoice_date || '-')}</td>
        </tr>
        <tr>
            <td class="meta-label">Job No</td>
            <td class="meta-value bold">${escapeHtml(bill.job_no || '-')}</td>
        </tr>
        <tr>
            <td class="meta-label">Customer</td>
            <td class="meta-value">${escapeHtml(bill.customer_name || '-')}</td>
        </tr>
        <tr>
            <td class="meta-label">Phone</td>
            <td class="meta-value">${escapeHtml(bill.customer_phone || '-')}</td>
        </tr>
    </table>

    <div class="divider-dashed"></div>

    <table class="items-table">
        <thead>
            <tr>
                <th class="b-batch">Batch</th>
                <th class="b-dress">Dress</th>
                <th class="b-qty">Qty</th>
                <th class="b-price">Price</th>
                <th class="b-total">Total</th>
            </tr>
        </thead>
        <tbody>
            ${rowsHtml || '<tr><td colspan="5" class="center" style="padding:8px 0;">No items</td></tr>'}
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td class="t-label">Sub Total</td>
            <td class="t-value">${formatMoney(bill.sub_total)}</td>
        </tr>
        <tr>
            <td class="t-label">Discount</td>
            <td class="t-value">${formatMoney(bill.discount)}</td>
        </tr>
        <tr class="grand-row">
            <td class="t-label">Grand Total</td>
            <td class="t-value">${formatMoney(bill.grand_total)}</td>
        </tr>
    </table>

    <div class="divider-dashed"></div>

    <div class="thanks">Thank You!</div>

    <div class="footer">
        Printed: ${escapeHtml(new Date().toLocaleString())}
    </div>

</body>
</html>
        `;

        const printWin = window.open('', '_blank', 'width=400,height=600');
        if (!printWin) {
            alert('Please allow popups to print the bill.');
            return;
        }

        printWin.document.open();
        printWin.document.write(html);
        printWin.document.close();

        printWin.onload = function () {
            printWin.focus();
            printWin.print();
        };
    }

    document.getElementById('btnPrintBill')?.addEventListener('click', async function () {
        try {
            const res = await fetch("{{ route('tailoring.jobs.billData', $job) }}", {
                headers: { "Accept": "application/json" }
            });
            const json = await res.json().catch(() => ({}));

            if (!json.success || !json.data) {
                alert('Could not load bill data.');
                return;
            }

            printJobBill(json.data);
        } catch (err) {
            alert('Error loading bill: ' + err);
        }
    });
</script>
@endsection