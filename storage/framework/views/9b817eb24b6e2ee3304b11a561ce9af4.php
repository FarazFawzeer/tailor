

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Handover', 'subtitle' => 'Group'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $jobNo   = $headerItem->jobBatch?->job?->job_no ?? '-';
        $batchNo = $headerItem->jobBatch?->batch_no ?? '-';
        $customer = $headerItem->jobBatch?->job?->customer?->full_name ?? 'N/A';
        $dress = $headerItem->dressType?->name ?? 'N/A';
    ?>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-1">Job: <?php echo e($jobNo); ?> | Batch: <?php echo e($batchNo); ?></h5>
            <p class="card-subtitle mb-0">
                Customer: <b><?php echo e($customer); ?></b> | Dress: <b><?php echo e($dress); ?></b> | Group: <b>#<?php echo e($groupId); ?></b>
            </p>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                <b>How to use:</b>
                Select the <b>From Stage</b> (example: Cutting) and enter qty to send.
                This will not affect other stages.
            </div>

            <div class="row g-2 mb-3">
                <?php $__currentLoopData = $stageSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-3">
                        <div class="border rounded p-2">
                            <div class="text-muted small"><?php echo e($s['stage_name']); ?></div>
                            <div class="fs-4 fw-bold"><?php echo e($s['qty']); ?></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div id="message"></div>

            <form id="groupHandoverForm">
                <?php echo csrf_field(); ?>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">From Stage</label>
                        <select name="from_stage_id" id="from_stage_id" class="form-select" required>
                            <option value="">Select stage</option>
                            <?php $__currentLoopData = $stageSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($s['stage_id']); ?>" data-qty="<?php echo e($s['qty']); ?>">
                                    <?php echo e($s['stage_name']); ?> (Available: <?php echo e($s['qty']); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <small class="text-muted">Choose which stage you are sending from.</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Qty to Handover</label>
                        <input type="number" class="form-control" name="qty" id="qty"
                               min="1" value="1" required>
                        <small class="text-muted">Max will be auto set based on selected stage.</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Received By (Next Stage Staff)</label>
                        <select name="received_by" class="form-select" required>
                            <option value="">Select Staff</option>
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?> (<?php echo e($u->email); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" class="form-control" rows="2"
                              placeholder="Example: Sent 2 pcs to sewing, remaining later..."></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        Back
                    </button>
                    <button class="btn btn-primary" type="submit">Save Handover</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const messageBox = document.getElementById('message');
        const fromStage = document.getElementById('from_stage_id');
        const qtyInput = document.getElementById('qty');

        fromStage.addEventListener('change', () => {
            const opt = fromStage.options[fromStage.selectedIndex];
            const maxQty = parseInt(opt.dataset.qty || "1");
            qtyInput.max = maxQty;
            qtyInput.value = Math.min(parseInt(qtyInput.value || "1"), maxQty);
        });

        document.getElementById('groupHandoverForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch("<?php echo e(route('tailoring.handover.group.store', $groupId)); ?>", {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>",
                    "Accept": "application/json"
                }
            }).then(async res => {
                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    messageBox.innerHTML = `<div class="alert alert-danger">${data.message || 'Validation error'}</div>`;
                    return;
                }

                messageBox.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                setTimeout(() => window.location.reload(), 700);
            }).catch(err => {
                messageBox.innerHTML = `<div class="alert alert-danger">Error: ${err}</div>`;
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Group Handover'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/tailoring/handover/group_create.blade.php ENDPATH**/ ?>