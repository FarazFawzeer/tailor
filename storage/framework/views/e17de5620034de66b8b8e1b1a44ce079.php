

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', [
        'title' => 'Tailoring Jobs',
        'subtitle' => 'Create (Easy Screen)',
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <style>
        .required-star {
            color: red;
            font-weight: bold;
            margin-left: 3px;
        }

        .batch-card {
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 12px;
        }

        .batch-header {
            background: rgba(0, 0, 0, .03);
            border-radius: 12px 12px 0 0;
        }

        .item-row-actions {
            min-width: 160px;
        }

        .muted-help {
            font-size: 12px;
            color: #6c757d;
        }

        .pill {
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 12px;
            background: rgba(13, 110, 253, .08);
        }

        /* Diagram styles */
        .diagram-wrap {
            position: relative;
            width: 100%;
        }

        .diagram-wrap img {
            width: 100%;
            height: auto;
            display: block;
        }

        .zone {
            position: absolute;
            border-radius: 10px;
            opacity: 0;
            transition: opacity .15s ease-in-out;
            outline: 2px dashed rgba(0, 0, 0, .35);
            background: rgba(255, 193, 7, 0.25);
            pointer-events: none;
        }

        .zone.active {
            opacity: 1;
        }

        .zone-neck {
            top: 6%;
            left: 38%;
            width: 24%;
            height: 10%;
        }

        .zone-shoulder {
            top: 12%;
            left: 20%;
            width: 60%;
            height: 12%;
        }

        .zone-chest {
            top: 25%;
            left: 25%;
            width: 50%;
            height: 16%;
        }

        .zone-sleeve {
            top: 20%;
            left: 5%;
            width: 20%;
            height: 22%;
        }

        .zone-waist {
            top: 42%;
            left: 28%;
            width: 44%;
            height: 14%;
        }

        .zone-hip {
            top: 55%;
            left: 28%;
            width: 44%;
            height: 14%;
        }

        .zone-length {
            top: 68%;
            left: 32%;
            width: 36%;
            height: 24%;
        }

        .zone-bottom {
            top: 86%;
            left: 32%;
            width: 36%;
            height: 10%;
        }

        .modal-diagram-card {
            position: sticky;
            top: 10px;
        }

        /* Price UI */
        .money {
            text-align: right;
        }

        .total-box {
            border: 1px dashed rgba(0, 0, 0, .15);
            border-radius: 12px;
            padding: 10px 12px;
            background: #fff;
            min-width: 220px;
        }

        .total-box .lbl {
            font-size: 12px;
            color: #6c757d;
        }

        .total-box .val {
            font-size: 18px;
            font-weight: 800;
        }

        /* ===== Items table responsive fix ===== */
        .items-table {
            min-width: 1250px;
            table-layout: auto;
        }

        .items-table th,
        .items-table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .items-table .qty-col {
            min-width: 120px;
            width: 120px;
        }

        .items-table .qtyInput {
            min-width: 90px;
            width: 100%;
        }

        .items-table .price-col,
        .items-table .total-col,
        .items-table .mode-col {
            min-width: 140px;
        }

        .items-table .notes-col {
            min-width: 220px;
            white-space: normal;
        }

        .items-table .action-col {
            min-width: 200px;
        }

        @media (max-width: 1366px) {
            .items-table {
                min-width: 1350px;
            }

            .items-table .qty-col {
                min-width: 130px;
                width: 130px;
            }

            .items-table .qtyInput {
                min-width: 100px;
            }
        }
    </style>
    <div class="card mb-3">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">Create Job</h5>
                    <div class="muted-help">Create Job → Add Batches → Add Items → Enter Measurements → Save once</div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <small class="text-muted d-block mb-3">
                Fields marked with <span class="required-star">*</span> are required.
            </small>

            <form id="wizardForm" action="<?php echo e(route('tailoring.jobs.storeWizard')); ?>" method="POST">
                <?php echo csrf_field(); ?>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Customer <span class="required-star">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($c->id); ?>"><?php echo e($c->full_name); ?> (<?php echo e($c->phone ?? '-'); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Job Date</label>
                        <input type="date" name="job_date" class="form-control" value="<?php echo e(now()->toDateString()); ?>">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Job Due Date <span class="required-star">*</span></label>
                        <input type="date" name="due_date" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes for this job"></textarea>
                </div>

                
                <div class="row g-2 mb-3">
                    <div class="col-md-4 ms-auto">
                        <div class="total-box text-end">
                            <div class="lbl">Grand Total (All Batches)</div>
                            <div class="val" id="grandTotalText">0.00</div>
                        </div>
                    </div>
                </div>

                <hr>

                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h5 class="mb-0">Batches</h5>
                        <div class="muted-help">You can add multiple batches here before saving.</div>
                    </div>
                    <button type="button" id="btnAddBatch" class="btn btn-outline-primary btn-sm">+ Add Batch</button>
                </div>

                <div id="batchesArea"></div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="<?php echo e(route('tailoring.jobs.index')); ?>" class="btn btn-secondary" style="width: 150px;">Back</a>
                    <button class="btn btn-primary" type="submit" style="width: 150px;">Save Job</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="modal fade" id="measurementModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0">Enter Measurements</h5>
                        <div class="muted-help" id="modalSubtitle"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div id="modalWarn" class="alert alert-warning d-none"></div>
                    <div id="modalBodyContent"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnSaveMeasurements">Save Measurements</button>
                </div>
            </div>
        </div>
    </div>

    <?php
        $dressTypesJs = [];
        foreach ($dressTypes as $d) {
            $dressTypesJs[] = [
                'id' => $d->id,
                'name' => $d->name,
                'code' => $d->code,
                'front_img' => $d->diagram_front
                    ? asset($d->diagram_front)
                    : asset('/images/diagrams/default-front.png'),
                'back_img' => $d->diagram_back ? asset($d->diagram_back) : asset('/images/diagrams/default-back.png'),
            ];
        }

        $templatesJs = [];
        foreach ($templates as $t) {
            $templatesJs[] = [
                'id' => $t->id,
                'name' => $t->name,
                'dress_type_id' => $t->dress_type_id,
                'dress_name' => optional($t->dressType)->name,
            ];
        }

        $defaultFront = asset('/images/diagrams/default-front.png');
        $defaultBack = asset('/images/diagrams/default-back.png');
    ?>

    <script>
        const DRESS_TYPES = <?php echo json_encode($dressTypesJs, 15, 512) ?>;
        const TEMPLATES = <?php echo json_encode($templatesJs, 15, 512) ?>;

        const DEFAULT_FRONT = <?php echo json_encode($defaultFront, 15, 512) ?>;
        const DEFAULT_BACK = <?php echo json_encode($defaultBack, 15, 512) ?>;

        const HIGHLIGHT_MAP = {
            chest: 'zone-chest',
            shoulder: 'zone-shoulder',
            sleeve_length: 'zone-sleeve',
            shirt_length: 'zone-length',
            neck: 'zone-neck',
            waist: 'zone-waist',
            hip: 'zone-hip',
            trouser_length: 'zone-length',
            bottom: 'zone-bottom',
        };

        const batchesArea = document.getElementById('batchesArea');
        const btnAddBatch = document.getElementById('btnAddBatch');

        let batchCount = 0;

        function optionDressTypes() {
            return `<option value="">Select</option>` + DRESS_TYPES.map(d =>
                `<option value="${d.id}">${d.name} (${d.code})</option>`
            ).join('');
        }

        function templateOptionsForDress(dressTypeId) {
            const list = TEMPLATES.filter(t => String(t.dress_type_id) === String(dressTypeId));
            let html = `<option value="">Select</option>`;
            html += list.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
            return html;
        }

        // ===== money helpers =====
        function money(n) {
            const x = Number(n || 0);
            return x.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function recalcRowTotals(row) {
            const qty = Number(row.querySelector('.qtyInput')?.value || 0);
            const unit = Number(row.querySelector('.unitPriceInput')?.value || 0);
            const line = qty * unit;
            row.querySelector('.lineTotalText').textContent = money(line);
            return line;
        }

        function recalcBatchTotals(batchCard) {
            let sum = 0;
            batchCard.querySelectorAll('tbody.itemsBody tr').forEach(r => {
                sum += recalcRowTotals(r);
            });

            const el = batchCard.querySelector('.batchTotalText');
            if (el) el.textContent = money(sum);
            return sum;
        }

        function recalcGrandTotal() {
            let total = 0;
            document.querySelectorAll('.batch-card').forEach(b => {
                total += recalcBatchTotals(b);
            });
            document.getElementById('grandTotalText').textContent = money(total);
        }

        // ===== hidden measurement store (REAL form inputs) =====
        function clearHiddenMeasurements(row) {
            row.querySelectorAll('.hidden-meas').forEach(el => el.remove());
        }

        function addHidden(row, name, value) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value ?? '';
            input.className = 'hidden-meas';
            row.appendChild(input);
        }

        // ===== Batches =====
        function addBatchCard() {
            const idx = batchCount++;

            const div = document.createElement('div');
            div.className = 'batch-card mb-3';
            div.dataset.batchIndex = idx;

            div.innerHTML = `
                <div class="batch-header p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <b>Batch #${idx + 1}</b>
                        <span class="text-muted ms-2">Add items inside this batch</span>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm btnRemoveBatch">Remove Batch</button>
                </div>

                <div class="p-3">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Batch Date</label>
                            <input type="date" class="form-control" name="batches[${idx}][batch_date]" value="<?php echo e(now()->toDateString()); ?>">
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="form-label">Batch Due Date <span class="required-star">*</span></label>
                            <input type="date" class="form-control" name="batches[${idx}][due_date]" required>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label">Batch Notes</label>
                            <input type="text" class="form-control" name="batches[${idx}][notes]" placeholder="Optional">
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <b>Items</b>
                            <div class="muted-help">Add dress + template + qty + price. Then click “Measurements”.</div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm btnAddItem">+ Add Item</button>
                    </div>

                    <div class="table-responsive">
                      <table class="table table-bordered align-middle items-table">
    <thead class="table-light">
        <tr>
            <th style="min-width:220px;">Dress Type <span class="required-star">*</span></th>
            <th style="min-width:220px;">Template</th>
            <th class="qty-col">Qty <span class="required-star">*</span></th>
            <th class="price-col">Unit Price</th>
            <th class="total-col">Line Total</th>
            <th class="mode-col">Mode</th>
            <th class="notes-col">Notes</th>
            <th class="action-col item-row-actions">Action</th>
        </tr>
    </thead>
    <tbody class="itemsBody"></tbody>
</table>
                    </div>

                    <div class="d-flex justify-content-end mt-2">
                        <div class="total-box text-end">
                            <div class="lbl">Batch Total</div>
                            <div class="val"><span class="batchTotalText">0.00</span></div>
                        </div>
                    </div>

                    <div class="text-muted small mt-2">
                        Tip: If Template selected, please enter Measurements for that item.
                    </div>
                </div>
            `;

            batchesArea.appendChild(div);
            addItemRow(div); // default 1 row

            recalcBatchTotals(div);
            recalcGrandTotal();
        }

        function addItemRow(batchCard) {
            const idx = batchCard.dataset.batchIndex;
            const tbody = batchCard.querySelector('.itemsBody');
            const itemIndex = tbody.querySelectorAll('tr').length;

            const tr = document.createElement('tr');
            tr.dataset.itemIndex = itemIndex;

     tr.innerHTML = `
    <td>
        <select class="form-select dressTypeSelect"
            name="batches[${idx}][items][${itemIndex}][dress_type_id]" required>
            ${optionDressTypes()}
        </select>
    </td>

    <td>
        <select class="form-select templateSelect"
            name="batches[${idx}][items][${itemIndex}][measurement_template_id]">
            <option value="">Select</option>
        </select>
    </td>

    <td class="qty-col">
        <input type="number" class="form-control qtyInput"
            name="batches[${idx}][items][${itemIndex}][qty]"
            value="1" min="1" required>
    </td>

    <td class="price-col">
        <input type="number" step="0.01" min="0"
            class="form-control unitPriceInput money"
            name="batches[${idx}][items][${itemIndex}][unit_price]"
            value="0">
    </td>

    <td class="money total-col">
        <span class="lineTotalText fw-bold">0.00</span>
    </td>

    <td class="mode-col">
        <div class="form-check mt-2">
            <input class="form-check-input perPieceCheck" type="checkbox"
                name="batches[${idx}][items][${itemIndex}][per_piece_measurement]" value="1">
            <label class="form-check-label">Per Piece</label>
        </div>
    </td>

    <td class="notes-col">
        <input type="text" class="form-control"
            name="batches[${idx}][items][${itemIndex}][notes]" placeholder="Optional">

        <div class="mt-2 measStore d-none"></div>
    </td>

    <td class="action-col">
        <div class="d-flex flex-column flex-md-row gap-2">
            <button type="button" class="btn btn-info btn-sm w-100 btnMeasurements">Measurements</button>
            <button type="button" class="btn btn-danger btn-sm w-100 btnRemoveItem">Remove</button>
        </div>
    </td>
`;

            tbody.appendChild(tr);

            recalcRowTotals(tr);
            recalcBatchTotals(batchCard);
            recalcGrandTotal();
        }

        // ===== modal =====
        const measurementModalEl = document.getElementById('measurementModal');
        const modalBodyContent = document.getElementById('modalBodyContent');
        const modalSubtitle = document.getElementById('modalSubtitle');
        const modalWarn = document.getElementById('modalWarn');
        const btnSaveMeasurements = document.getElementById('btnSaveMeasurements');

        let currentRow = null;
        let currentPrefix = null;

        function buildDiagramHtml(frontImg, backImg) {
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
                                Click a measurement field to highlight area.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div id="modalFormArea"></div>
                </div>
            </div>`;
        }

        function buildMeasurementFormHtml(fields, prefix, qty, perPiece) {
            if (!fields.length) {
                return `<div class="alert alert-warning">This template has no fields.</div>`;
            }

            const inputType = (f) => (f.input_type === 'text' ? 'text' : 'number');
            const reqStar = (f) => f.is_required ? `<span class="required-star">*</span>` : ``;
            const zoneFor = (f) => (f.key && HIGHLIGHT_MAP[f.key]) ? HIGHLIGHT_MAP[f.key] : '';

            if (!perPiece) {
                return `
                    <div class="card border">
                        <div class="card-header"><b>Same measurements for all pieces (Qty ${qty})</b></div>
                        <div class="card-body">
                            <div class="row">
                                ${fields.map(f => `
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">
                                                ${f.label} <small class="text-muted">(${f.unit})</small> ${reqStar(f)}
                                            </label>
                                            <input
                                                type="${inputType(f)}"
                                                step="0.01"
                                                class="form-control measure-field"
                                                data-zone="${zoneFor(f)}"
                                                name="${prefix}[measurements][same][${f.id}]"
                                                placeholder="Enter ${f.label}">
                                        </div>
                                    `).join('')}
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Notes (optional)</label>
                                <input class="form-control" name="${prefix}[notes_map][same]" placeholder="Optional">
                            </div>
                        </div>
                    </div>
                `;
            }

            let tabs = '';
            let panes = '';

            for (let p = 1; p <= qty; p++) {
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

                panes += `
                    <div class="tab-pane fade ${p===1?'show active':''}" id="pane-${p}" role="tabpanel">
                        <div class="card border mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <b>Piece ${p}</b>
                                <span class="text-muted small">Enter measurements for this piece</span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    ${fields.map(f => `
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">
                                                    ${f.label} <small class="text-muted">(${f.unit})</small> ${reqStar(f)}
                                                </label>
                                                <input
                                                    type="${inputType(f)}"
                                                    step="0.01"
                                                    class="form-control measure-field"
                                                    data-zone="${zoneFor(f)}"
                                                    name="${prefix}[measurements][${p}][${f.id}]"
                                                    placeholder="Enter ${f.label}">
                                            </div>
                                        `).join('')}
                                </div>

                                <div class="mb-0">
                                    <label class="form-label">Notes (optional)</label>
                                    <input class="form-control" name="${prefix}[notes_map][${p}]" placeholder="Optional">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            return `
                <ul class="nav nav-tabs mb-3" style="overflow-x:auto; flex-wrap:nowrap;">
                    ${tabs}
                </ul>
                <div class="tab-content">${panes}</div>
            `;
        }

        function clearZones() {
            modalBodyContent.querySelectorAll('.zone').forEach(z => z.classList.remove('active'));
        }

        function enableDiagramSwitching(frontImg, backImg) {
            const diagramImage = modalBodyContent.querySelector('#diagramImage');
            const btnFront = modalBodyContent.querySelector('#btnFront');
            const btnBack = modalBodyContent.querySelector('#btnBack');

            btnFront?.addEventListener('click', () => {
                diagramImage.src = frontImg;
                btnFront.classList.add('active');
                btnBack.classList.remove('active');
                clearZones();
            });

            btnBack?.addEventListener('click', () => {
                diagramImage.src = backImg;
                btnBack.classList.add('active');
                btnFront.classList.remove('active');
                clearZones();
            });
        }

        let modalHighlightBound = false;

        function enableFieldHighlightingOnce() {
            if (modalHighlightBound) return;
            modalHighlightBound = true;

            modalBodyContent.addEventListener('focusin', function(e) {
                if (!e.target.classList.contains('measure-field')) return;
                const zone = e.target.dataset.zone;
                clearZones();
                if (!zone) return;
                const el = modalBodyContent.querySelector('.' + zone);
                if (el) el.classList.add('active');
            });

            modalBodyContent.addEventListener('click', function(e) {
                if (!e.target.classList.contains('measure-field')) return;
                const zone = e.target.dataset.zone;
                clearZones();
                if (!zone) return;
                const el = modalBodyContent.querySelector('.' + zone);
                if (el) el.classList.add('active');
            });

            modalBodyContent.addEventListener('focusout', function(e) {
                if (!e.target.classList.contains('measure-field')) return;
                setTimeout(clearZones, 120);
            });
        }

        async function openMeasurementsForRow(row) {
            currentRow = row;

            const batchCard = row.closest('.batch-card');
            const batchIndex = batchCard.dataset.batchIndex;
            const itemIndex = row.dataset.itemIndex;

            currentPrefix = `batches[${batchIndex}][items][${itemIndex}]`;

            const dressTypeId = row.querySelector('.dressTypeSelect').value;
            const templateId = row.querySelector('.templateSelect').value;
            const qty = parseInt(row.querySelector('.qtyInput').value || '1', 10);
            const perPiece = row.querySelector('.perPieceCheck').checked;

            const dressObj = DRESS_TYPES.find(d => String(d.id) === String(dressTypeId));
            const frontImg = dressObj?.front_img || DEFAULT_FRONT;
            const backImg = dressObj?.back_img || DEFAULT_BACK;

            const dressName = row.querySelector('.dressTypeSelect')?.selectedOptions?.[0]?.textContent ?? '';
            modalSubtitle.textContent = `${dressName} | Qty ${qty} | ${perPiece ? 'Per Piece' : 'Same for All'}`;

            modalWarn.classList.add('d-none');
            modalWarn.textContent = '';

            modalBodyContent.innerHTML = buildDiagramHtml(frontImg, backImg);
            const modalFormArea = modalBodyContent.querySelector('#modalFormArea');

            if (!templateId) {
                modalFormArea.innerHTML =
                    `<div class="alert alert-warning">Please select a Measurement Template first.</div>`;
                btnSaveMeasurements.disabled = true;
            } else {
                btnSaveMeasurements.disabled = false;

                const res = await fetch(`<?php echo e(url('measurement-templates')); ?>/${templateId}/fields`, {
                    headers: {
                        "Accept": "application/json"
                    }
                });

                const json = await res.json().catch(() => ({}));
                const fields = json?.data || [];

                modalFormArea.innerHTML = buildMeasurementFormHtml(fields, currentPrefix, qty, perPiece);

                // ✅ If already saved hidden inputs exist, re-fill inputs in modal
                const hiddenInputs = row.querySelectorAll('input.hidden-meas');
                hiddenInputs.forEach(h => {
                    const target = modalFormArea.querySelector(`[name="${CSS.escape(h.name)}"]`);
                    if (target) target.value = h.value;
                });
            }

            enableDiagramSwitching(frontImg, backImg);
            enableFieldHighlightingOnce();

            const modal = new bootstrap.Modal(measurementModalEl);
            modal.show();
        }

        // ✅ save measurements into hidden inputs (REAL submit values)
        btnSaveMeasurements.addEventListener('click', function() {
            if (!currentRow) return;

            const templateId = currentRow.querySelector('.templateSelect').value;
            if (templateId) {
                const anyInput = modalBodyContent.querySelector('input[name*="[measurements]"]');
                if (!anyInput) {
                    modalWarn.textContent = "This template has no measurement fields.";
                    modalWarn.classList.remove('d-none');
                    return;
                }
            }

            clearHiddenMeasurements(currentRow);

            const modalFormArea = modalBodyContent.querySelector('#modalFormArea');

            modalFormArea.querySelectorAll('input[name*="[measurements]"]').forEach(inp => {
                addHidden(currentRow, inp.name, inp.value);
            });

            modalFormArea.querySelectorAll('input[name*="[notes_map]"]').forEach(inp => {
                addHidden(currentRow, inp.name, inp.value);
            });

            const store = currentRow.querySelector('.measStore');
            store.innerHTML = `<span class="badge bg-success">Measurements Saved</span>`;
            store.classList.remove('d-none');

            bootstrap.Modal.getInstance(measurementModalEl).hide();
        });

        // ===== Events =====
        btnAddBatch.addEventListener('click', function() {
            addBatchCard();
        });

        batchesArea.addEventListener('click', function(e) {
            const batchCard = e.target.closest('.batch-card');

            if (e.target.closest('.btnRemoveBatch')) {
                batchCard?.remove();
                recalcGrandTotal();
                return;
            }

            if (e.target.closest('.btnAddItem')) {
                addItemRow(batchCard);
                return;
            }

            if (e.target.closest('.btnRemoveItem')) {
                const row = e.target.closest('tr');
                row?.remove();
                recalcBatchTotals(batchCard);
                recalcGrandTotal();
                return;
            }

            if (e.target.closest('.btnMeasurements')) {
                openMeasurementsForRow(e.target.closest('tr'));
                return;
            }
        });

        // dress type change -> filter templates + clear stored hidden measurements
        batchesArea.addEventListener('change', function(e) {
            if (!e.target.classList.contains('dressTypeSelect')) return;

            const row = e.target.closest('tr');
            const templateSelect = row.querySelector('.templateSelect');
            const dressTypeId = e.target.value;

            templateSelect.innerHTML = templateOptionsForDress(dressTypeId);

            row.querySelector('.measStore').innerHTML = '';
            clearHiddenMeasurements(row);
        });

        // template change -> clear stored hidden measurements
        batchesArea.addEventListener('change', function(e) {
            if (!e.target.classList.contains('templateSelect')) return;
            const row = e.target.closest('tr');
            row.querySelector('.measStore').innerHTML = '';
            clearHiddenMeasurements(row);
        });

        // per piece or qty change -> clear stored hidden measurements
        batchesArea.addEventListener('change', function(e) {
            if (e.target.classList.contains('perPieceCheck') || e.target.classList.contains('qtyInput')) {
                const row = e.target.closest('tr');
                row.querySelector('.measStore').innerHTML = '';
                clearHiddenMeasurements(row);
            }
        });

        // ✅ price + qty live totals
        batchesArea.addEventListener('input', function(e) {
            if (e.target.classList.contains('qtyInput') || e.target.classList.contains('unitPriceInput')) {
                const row = e.target.closest('tr');
                const batchCard = e.target.closest('.batch-card');
                recalcRowTotals(row);
                recalcBatchTotals(batchCard);
                recalcGrandTotal();
            }
        });

        // ✅ default first batch
        addBatchCard();

        // ===== Submit wizard =====
        document.getElementById('wizardForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const box = document.getElementById('message');
            box.innerHTML = '';

            // ✅ validate: template selected => must have hidden measurement inputs
            const rows = batchesArea.querySelectorAll('tr');
            for (const r of rows) {
                const templateId = r.querySelector('.templateSelect')?.value;
                if (templateId) {
                    const hidden = r.querySelectorAll('input.hidden-meas[name*="[measurements]"]');
                    if (!hidden || hidden.length === 0) {
                        box.innerHTML = `<div class="alert alert-danger">
                            Please enter measurements for all items that have a template selected.
                        </div>`;
                        return;
                    }
                }
            }

            const formData = new FormData(this);

            fetch(this.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                    "Accept": "application/json"
                }
            }).then(async res => {
                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    box.innerHTML =
                        `<div class="alert alert-danger">${data.message || 'Validation error'}</div>`;
                    return;
                }

                box.innerHTML = `<div class="alert alert-success">${data.message}</div>`;

                setTimeout(() => {
                    window.location.href = "<?php echo e(url('tailoring/jobs')); ?>/" + data.data.id;
                }, 700);

            }).catch(err => {
                box.innerHTML = `<div class="alert alert-danger">Error: ${err}</div>`;
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Create Job (Easy)'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/tailoring/jobs/create_wizard.blade.php ENDPATH**/ ?>