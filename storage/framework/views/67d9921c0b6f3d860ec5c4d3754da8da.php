

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Measurements', 'subtitle' => 'Entry'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        // Diagram images from DressType (fallback to defaults)
$frontImg = $item->dressType?->diagram_front ? asset($item->dressType->diagram_front) : asset('/images/diagrams/default-front.png');
$backImg  = $item->dressType?->diagram_back  ? asset($item->dressType->diagram_back)  : asset('/images/diagrams/default-back.png');

        // Map measurement field "key" -> zone class
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
    ?>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Job: <?php echo e($job->job_no); ?> | <?php echo e($batch->batch_no); ?></h5>
            <p class="card-subtitle mb-0">
                Dress: <b><?php echo e($item->dressType?->name); ?></b> |
                Template: <b><?php echo e($item->measurementTemplate?->name); ?></b> |
                Qty: <b><?php echo e($item->qty); ?></b> |
                Mode:
                <?php if($item->per_piece_measurement): ?>
                    <span class="badge bg-warning">Per Piece</span>
                <?php else: ?>
                    <span class="badge bg-success">Same for All</span>
                <?php endif; ?>
            </p>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <form id="measurementForm">
                <?php echo csrf_field(); ?>

                <div class="row">
                    
                    <div class="col-md-4 mb-3">
                        <div class="card border h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <b>Diagram</b>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-dark active" id="btnFront">Front</button>
                                    <button type="button" class="btn btn-outline-dark" id="btnBack">Back</button>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="diagram-wrap">
                                    <img id="diagramImage" src="<?php echo e($frontImg); ?>" alt="Diagram" class="img-fluid rounded">

                                    
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

                                <?php if($item->per_piece_measurement): ?>
                                    <div class="mt-2 small">
                                        <span class="badge bg-info">Tip</span>
                                        Use Piece tabs to enter different measurements.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-md-8">

                        <?php if(!$item->per_piece_measurement): ?>
                            
                            <div class="card border mb-3">
                                <div class="card-header">
                                    <b>Same measurements for all pieces (Qty <?php echo e($item->qty); ?>)</b>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $saved = $existing['same'][$f->id] ?? '';
                                                $zoneClass = $highlightMap[$f->key] ?? '';
                                            ?>

                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">
                                                    <?php echo e($f->label); ?>

                                                    <small class="text-muted">(<?php echo e($f->unit); ?>)</small>
                                                    <?php if($f->is_required): ?> <span class="text-danger">*</span> <?php endif; ?>
                                                </label>

                                                <input
                                                    type="<?php echo e($f->input_type === 'text' ? 'text' : 'number'); ?>"
                                                    step="0.01"
                                                    class="form-control measure-field"
                                                    data-zone="<?php echo e($zoneClass); ?>"
                                                    name="measurements[same][<?php echo e($f->id); ?>]"
                                                    value="<?php echo e($saved); ?>"
                                                    placeholder="Enter <?php echo e($f->label); ?>">
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label">Notes (optional)</label>
                                        <input class="form-control" name="notes[same]" value="<?php echo e($existing['same']['_notes'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                        <?php else: ?>
                            
                            <ul class="nav nav-tabs mb-3" id="pieceTabs" role="tablist">
                                <?php for($p = 1; $p <= $item->qty; $p++): ?>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link <?php echo e($p === 1 ? 'active' : ''); ?>"
                                                id="piece-tab-<?php echo e($p); ?>"
                                                data-bs-toggle="tab"
                                                data-bs-target="#piece-pane-<?php echo e($p); ?>"
                                                type="button"
                                                role="tab"
                                                aria-controls="piece-pane-<?php echo e($p); ?>"
                                                aria-selected="<?php echo e($p === 1 ? 'true' : 'false'); ?>">
                                            Piece <?php echo e($p); ?>

                                        </button>
                                    </li>
                                <?php endfor; ?>
                            </ul>

                            <div class="tab-content" id="pieceTabContent">
                                <?php for($p = 1; $p <= $item->qty; $p++): ?>
                                    <?php
                                        $pieceKey = (string)$p;
                                    ?>

                                    <div class="tab-pane fade <?php echo e($p === 1 ? 'show active' : ''); ?>"
                                         id="piece-pane-<?php echo e($p); ?>"
                                         role="tabpanel"
                                         aria-labelledby="piece-tab-<?php echo e($p); ?>">

                                        <div class="card border mb-3">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <b>Piece <?php echo e($p); ?> Measurements</b>
                                                <span class="text-muted small">Enter actual values for this piece</span>
                                            </div>

                                            <div class="card-body">
                                                <div class="row">
                                                    <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $saved = $existing[$pieceKey][$f->id] ?? '';
                                                            $zoneClass = $highlightMap[$f->key] ?? '';
                                                        ?>

                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">
                                                                <?php echo e($f->label); ?>

                                                                <small class="text-muted">(<?php echo e($f->unit); ?>)</small>
                                                                <?php if($f->is_required): ?> <span class="text-danger">*</span> <?php endif; ?>
                                                            </label>

                                                            <input
                                                                type="<?php echo e($f->input_type === 'text' ? 'text' : 'number'); ?>"
                                                                step="0.01"
                                                                class="form-control measure-field"
                                                                data-zone="<?php echo e($zoneClass); ?>"
                                                                name="measurements[<?php echo e($pieceKey); ?>][<?php echo e($f->id); ?>]"
                                                                value="<?php echo e($saved); ?>"
                                                                placeholder="Enter <?php echo e($f->label); ?>">
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>

                                                <div class="mb-0">
                                                    <label class="form-label">Notes (optional)</label>
                                                    <input class="form-control" name="notes[<?php echo e($pieceKey); ?>]"
                                                           value="<?php echo e($existing[$pieceKey]['_notes'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?php echo e(route('tailoring.jobs.show', $job)); ?>" class="btn btn-secondary">Back</a>
                            <button class="btn btn-primary" type="submit">Save Measurements</button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>

    
    <style>
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

        /* Approximate positions - adjust based on your diagram */
        .zone-neck { top: 6%; left: 38%; width: 24%; height: 10%; }
        .zone-shoulder { top: 12%; left: 20%; width: 60%; height: 12%; }
        .zone-chest { top: 25%; left: 25%; width: 50%; height: 16%; }
        .zone-sleeve { top: 20%; left: 5%; width: 20%; height: 22%; }
        .zone-waist { top: 42%; left: 28%; width: 44%; height: 14%; }
        .zone-hip { top: 55%; left: 28%; width: 44%; height: 14%; }
        .zone-length { top: 68%; left: 32%; width: 36%; height: 24%; }
        .zone-bottom { top: 86%; left: 32%; width: 36%; height: 10%; }

        /* Tabs scroll if qty large */
        #pieceTabs { overflow-x: auto; flex-wrap: nowrap; }
        #pieceTabs .nav-link { white-space: nowrap; }
    </style>

    <script>
        // Diagram front/back switching
        const frontImg = <?php echo json_encode($frontImg, 15, 512) ?>;
        const backImg  = <?php echo json_encode($backImg, 15, 512) ?>;

        const diagramImage = document.getElementById('diagramImage');
        const btnFront = document.getElementById('btnFront');
        const btnBack = document.getElementById('btnBack');

        function clearZones() {
            document.querySelectorAll('.zone').forEach(z => z.classList.remove('active'));
        }

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

        // Highlight zones on focus/click
        document.addEventListener('focusin', function(e) {
            if (!e.target.classList.contains('measure-field')) return;
            const zone = e.target.dataset.zone;
            clearZones();
            if (!zone) return;
            const el = document.querySelector('.' + zone);
            if (el) el.classList.add('active');
        });

        document.addEventListener('click', function(e) {
            if (!e.target.classList.contains('measure-field')) return;
            const zone = e.target.dataset.zone;
            clearZones();
            if (!zone) return;
            const el = document.querySelector('.' + zone);
            if (el) el.classList.add('active');
        });

        document.addEventListener('focusout', function(e) {
            if (!e.target.classList.contains('measure-field')) return;
            setTimeout(clearZones, 120);
        });

        // Save measurements (AJAX)
        document.getElementById('measurementForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch("<?php echo e(route('tailoring.measurements.store', [$job, $batch, $item])); ?>", {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>",
                    "Accept": "application/json"
                }
            }).then(async res => {
                const data = await res.json().catch(() => ({}));
                const msg = document.getElementById('message');

                if (!res.ok) {
                    msg.innerHTML = `<div class="alert alert-danger">${data.message || 'Validation error'}</div>`;
                    return;
                }

                msg.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                setTimeout(() => msg.innerHTML = "", 2500);
            }).catch(err => {
                document.getElementById('message').innerHTML =
                    `<div class="alert alert-danger">Error: ${err}</div>`;
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Measurements'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/tailoring/measurements/edit.blade.php ENDPATH**/ ?>