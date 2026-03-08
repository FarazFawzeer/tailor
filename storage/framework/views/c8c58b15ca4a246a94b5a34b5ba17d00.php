

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Tailoring Jobs', 'subtitle' => 'View (Easy Screen)'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php
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
?>

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


<div class="card info-card mb-3">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
            <div>
                <h5 class="card-title mb-1">Job: <?php echo e($job->job_no); ?></h5>
           <div class="muted-help">Create → Measurements → Handover stages → Delivered → Complete → Invoice</div>
            </div>
            <div class="d-flex gap-2 align-items-center">
   
            <a href="<?php echo e(route('tailoring.jobs.index')); ?>" class="btn btn-outline-secondary btn-sm" style="width: 100px;">Back</a>
      
                <a href="<?php echo e(route('tailoring.jobs.editWizard', $job)); ?>" class="btn btn-outline-primary btn-sm" style="width: 100px;">Edit </a>
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
                                <div class="fw-semibold"><?php echo e($job->customer?->full_name ?? '-'); ?></div>
                                <div class="text-muted mini">
                                    Phone: <?php echo e($job->customer?->phone ?? '-'); ?>

                                    <?php if(!empty($job->customer?->email)): ?>
                                        | Email: <?php echo e($job->customer?->email); ?>

                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 text-md-end">
                        <span class="stage-pill">
                            Current Stage:
                            <b><?php echo e($job->currentStage?->name ?? '-'); ?></b>
                        </span>
                    </div>

                    <div class="col-md-3">
                        <div class="kv"><b>Job Date:</b> <span><?php echo e($job->job_date?->format('d M Y') ?? '-'); ?></span></div>
                    </div>
                    <div class="col-md-3">
                        <div class="kv"><b>Due Date:</b> <span><?php echo e($job->due_date?->format('d M Y') ?? '-'); ?></span></div>
                    </div>
                    <div class="col-md-">
                        <div class="kv"><b>Notes:</b> <span><?php echo e($job->notes ?? '-'); ?></span></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="price-box">
                    <div class="label">Total Job Amount</div>
                    <div class="val"><?php echo e(number_format((float)$totalAmount, 2)); ?></div>
                    <div class="muted-help">Based on Qty × Unit Price</div>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        
       <a class="btn btn-success btn-sm text-white" 
   href="<?php echo e(route('tailoring.jobs.invoicePdf', $job)); ?>"
   target="_blank">
   Generate Invoice
</a>
                    </div>
                </div>
            </div>
        </div>

        
    </div>
</div>


<div class="card info-card mb-3">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Stage Summary</h5>
                <div class="muted-help">Total qty & items in each stage</div>
            </div>
     <span class="pill"><?php echo e($stages->count()); ?> Stages</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-2 flex-nowrap" style="overflow-x:auto;">
            <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $stat = $stageStats[$s->id] ?? ['items_count'=>0,'qty_sum'=>0];
                ?>
                <div class="col" style="min-width:190px;">
                    <div class="stage-chip">
                        <div class="d-flex justify-content-between">
                            <div class="name"><?php echo e($s->name); ?></div>
                            <span class="badge bg-light text-dark">Stage <?php echo e($s->sort_order); ?></span>
                        </div>
                        <div class="qty mt-2"><?php echo e((int)$stat['qty_sum']); ?></div>
                        <div class="meta"><?php echo e((int)$stat['items_count']); ?> items</div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>


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
        <?php $__empty_1 = true; $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="group-card mb-3">
                <div class="group-head">
                    <div>
                        <div class="fw-semibold">
                           <?php echo e($g['dress_name']); ?>

                            <span class="text-muted mini">| Total Qty: <b><?php echo e($g['total_qty']); ?></b></span>
                        </div>
                        <div class="text-muted mini">
                            Template: <?php echo e($g['template_name']); ?>

                            | Unit Price: <?php echo e(number_format((float)$g['unit_price'], 2)); ?>

                            | Total: <b><?php echo e(number_format((float)$g['line_total'], 2)); ?></b>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?php echo e(route('tailoring.handover.group.create', $g['group_id'])); ?>" class="btn btn-primary btn-sm" style="width: 150px">
                            Group Handover
                        </a>

                        <?php if(!empty($g['history_item_id'])): ?>
                            <a href="<?php echo e(route('tailoring.handover.history', $g['history_item_id'])); ?>" class="btn btn-outline-dark btn-sm" style="width: 150px">
                                History
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="group-body">
                    
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $sq = (int)($g['stage_qty'][$s->id] ?? 0); ?>
                            <span class="stage-pill">
                                <?php echo e($s->name); ?>:
                                <b><?php echo e($sq); ?></b>
                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th >Batch</th>
                                    <th >Qty</th>
                                    <th >Stage</th>
                                    <th>Notes</th>
                                    <th style="width:300px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $g['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($row->jobBatch?->batch_no ?? '-'); ?></td>
                                        <td class="fw-bold"><?php echo e($row->qty); ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo e($row->stage?->name ?? 'N/A'); ?></span>
                                            <?php if($row->parent_item_id): ?>
                                                <span class="badge bg-warning ms-1">Part</span>
                                            <?php endif; ?>
                                            <?php if($row->completed_at): ?>
                                                <span class="badge bg-success ms-1">Completed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-muted"><?php echo e($row->notes ?? '-'); ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a class="btn btn-outline-primary btn-sm w-100 <?php echo e($row->completed_at ? 'disabled' : ''); ?>"
                                                   href="<?php echo e($row->completed_at ? '#' : route('tailoring.handover.create', $row)); ?>" style="width: 150px;">
                                                    Single Handover
                                                </a>

                                                <a class="btn btn-outline-dark btn-sm w-100"
                                                   href="<?php echo e(route('tailoring.measurements.edit', [$job, $row->jobBatch, $row])); ?>"  style="width: 150px;">
                                                    Measurements
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center text-muted py-4">No items found.</div>
        <?php endif; ?>
    </div>
</div>


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
        <?php $__empty_1 = true; $__currentLoopData = $job->batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="border rounded-3 p-3 mb-3">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                    <div>
                        <div class="fw-semibold"><?php echo e($batch->batch_no); ?></div>
                        <div class="text-muted mini">
                            Batch Date: <?php echo e($batch->batch_date?->format('d M Y') ?? '-'); ?>

                            | Due: <?php echo e($batch->due_date?->format('d M Y') ?? '-'); ?>

                            <?php if($batch->notes): ?> | Notes: <?php echo e($batch->notes); ?> <?php endif; ?>
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
                                <th >Unit Price</th>
                                <th >Line Total</th>
                                <th >Item Notes</th>
                                <th style="width:300px;">Measurements</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_2 = true; $__currentLoopData = $batch->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                <?php
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
                                ?>

                                <tr>
                                    <td><?php echo e($it->dressType?->name ?? '-'); ?></td>
                                    <td><?php echo e($it->measurementTemplate?->name ?? '-'); ?></td>
                                    <td><?php echo e($it->qty); ?></td>
                                    <td>
                                        <?php if($it->per_piece_measurement): ?>
                                            <span class="badge bg-warning">Per Piece</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Same</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e(number_format((float)$it->unit_price, 2)); ?></td>
                                    <td class="fw-bold"><?php echo e(number_format((float)$it->line_total, 2)); ?></td>
                                    <td><?php echo e($it->notes ?? '-'); ?></td>
                                    <td>
                                        <div class="d-grid gap-2">
                                            <?php if($payload): ?>
                                                <button type="button"
                                                    class="btn btn-info btn-sm btnViewMeasurements"
                                                    data-payload='<?php echo json_encode($payload, 15, 512) ?>'>
                                                    View in Screen
                                                </button>
                                            <?php endif; ?>

                                            <a class="btn btn-outline-dark btn-sm"
                                                href="<?php echo e(route('tailoring.measurements.edit', [$job, $batch, $it])); ?>">
                                                Open Measurements 
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No items in this batch.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center text-muted py-4">No batches yet.</div>
        <?php endif; ?>
    </div>
</div>


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
    const HIGHLIGHT_MAP = <?php echo json_encode($highlightMap, 15, 512) ?>;

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
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Job View (Easy)'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/tailoring/jobs/show.blade.php ENDPATH**/ ?>