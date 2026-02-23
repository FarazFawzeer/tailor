<?php
    $dressTypes = \App\Models\DressType::where('is_active', true)->orderBy('name')->get();
    $templates = \App\Models\MeasurementTemplate::where('is_active', true)->with('dressType')->get();
?>

<div class="card mb-3" id="batch-<?php echo e($batch->id); ?>">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0"><?php echo e($batch->batch_no); ?></h5>
            <small class="text-muted">
                Batch Date: <?php echo e($batch->batch_date?->format('d M Y') ?? '-'); ?> |
                Due: <?php echo e($batch->due_date?->format('d M Y') ?? '-'); ?>

            </small>
        </div>

        <button class="btn btn-danger btn-sm delete-batch" data-batch="<?php echo e($batch->id); ?>">
            Delete Batch
        </button>
    </div>

    <div class="card-body">
        <div id="itemMessage-<?php echo e($batch->id); ?>"></div>

        
        <form class="addItemForm" data-batch="<?php echo e($batch->id); ?>">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">Dress Type</label>
                    <select name="dress_type_id" class="form-select" required>
                        <option value="">Select</option>
                        <?php $__currentLoopData = $dressTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($d->id); ?>"><?php echo e($d->name); ?> (<?php echo e($d->code); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-3 mb-2">
                    <label class="form-label">Measurement Template</label>
                    <select name="measurement_template_id" class="form-select">
                        <option value="">Select</option>
                        <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($t->id); ?>"><?php echo e($t->dressType?->name); ?> - <?php echo e($t->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-2 mb-2">
                    <label class="form-label">Qty</label>
                    <input type="number" name="qty" class="form-control" value="1" min="1" required>
                </div>

                <div class="col-md-2 mb-2">
                    <label class="form-label d-block">Measurement</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="per_piece_measurement" value="1">
                        <label class="form-check-label">Per piece</label>
                    </div>
                </div>

                <div class="col-md-2 mb-2">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="Optional">
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button class="btn btn-primary" type="submit">+ Add Item</button>
            </div>
        </form>

        <hr>

        
        <div class="table-responsive">
            <table class="table table-hover table-centered">
                <thead class="table-light">
                    <tr>
                        <th>Dress</th>
                        <th>Template</th>
                        <th>Qty</th>
                        <th>Per Piece?</th>
                        <th>Notes</th>
                        <th style="width: 120px;">Action</th>
                    </tr>
                </thead>
                <tbody id="itemsBody-<?php echo e($batch->id); ?>">
                    <?php $__empty_1 = true; $__currentLoopData = $batch->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr id="item-<?php echo e($it->id); ?>">
                            <td><?php echo e($it->dressType?->name); ?></td>
                            <td><?php echo e($it->measurementTemplate?->name ?? '-'); ?></td>
                            <td><?php echo e($it->qty); ?></td>
                            <td>
                                <?php if($it->per_piece_measurement): ?>
                                    <span class="badge bg-warning">Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Same</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($it->notes ?? '-'); ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-info btn-sm w-100"
                                        href="<?php echo e(route('tailoring.measurements.edit', [$job, $batch, $it])); ?>">
                                        Measurements
                                    </a>

                                    <button class="btn btn-danger btn-sm w-100 delete-item"
                                        data-batch="<?php echo e($batch->id); ?>" data-item="<?php echo e($it->id); ?>">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr id="noItemsRow-<?php echo e($batch->id); ?>">
                            <td colspan="6" class="text-center text-muted">No items yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Add Item
    document.querySelectorAll('.addItemForm').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const batchId = this.dataset.batch;
            const formData = new FormData(this);

            fetch("<?php echo e(url('tailoring/jobs/' . $job->id . '/batches')); ?>/" + batchId + "/items", {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>",
                    "Accept": "application/json"
                }
            }).then(async res => {
                const data = await res.json().catch(() => ({}));
                const box = document.getElementById('itemMessage-' + batchId);

                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
                        const errors = Object.values(data.errors).flat().join('<br>');
                        box.innerHTML = `<div class="alert alert-danger">${errors}</div>`;
                        return;
                    }
                    box.innerHTML =
                        `<div class="alert alert-danger">Something went wrong.</div>`;
                    return;
                }

                box.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                setTimeout(() => box.innerHTML = "", 2000);

                document.getElementById('noItemsRow-' + batchId)?.remove();

                // simplest: reload to reflect template name nicely
                setTimeout(() => window.location.reload(), 600);
            });
        });
    });

    // Delete Batch
    document.querySelectorAll('.delete-batch').forEach(btn => {
        btn.addEventListener('click', function() {
            const batchId = this.dataset.batch;

            Swal.fire({
                title: 'Delete batch?',
                text: "This batch and its items will be deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (!result.isConfirmed) return;

                fetch("<?php echo e(url('tailoring/jobs/' . $job->id . '/batches')); ?>/" + batchId, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>",
                        "Accept": "application/json"
                    }
                }).then(async res => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.success) {
                        Swal.fire('Error!', data.message || 'Something went wrong!',
                            'error');
                        return;
                    }
                    document.getElementById('batch-' + batchId)?.remove();
                    Swal.fire('Deleted!', data.message, 'success');
                });
            });
        });
    });

    // Delete Item
    document.querySelectorAll('.delete-item').forEach(btn => {
        btn.addEventListener('click', function() {
            const batchId = this.dataset.batch;
            const itemId = this.dataset.item;

            Swal.fire({
                title: 'Delete item?',
                text: "This item will be removed from batch!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (!result.isConfirmed) return;

                fetch("<?php echo e(url('tailoring/jobs/' . $job->id . '/batches')); ?>/" + batchId +
                    "/items/" + itemId, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>",
                            "Accept": "application/json"
                        }
                    }).then(async res => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.success) {
                        Swal.fire('Error!', data.message || 'Something went wrong!',
                            'error');
                        return;
                    }
                    document.getElementById('item-' + itemId)?.remove();
                    Swal.fire('Deleted!', data.message, 'success');
                });
            });
        });
    });
</script>
<?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/tailoring/jobs/partials/batch_card.blade.php ENDPATH**/ ?>