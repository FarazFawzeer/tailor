

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Dress Types', 'subtitle' => 'Edit'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Edit Dress Type</h5>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <form id="editDressTypeForm"
                action="<?php echo e(route('dress-types.update', $dressType)); ?>"
                method="POST"
                enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Code</label>
                        <input name="code" class="form-control" value="<?php echo e($dressType->code); ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name</label>
                        <input name="name" class="form-control" value="<?php echo e($dressType->name); ?>" required>
                    </div>
                </div>

                <div class="row">
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Diagram Front (optional)</label>
                        <input type="file" name="diagram_front" class="form-control" accept="image/*">
                        <small class="text-muted">Upload new image to replace existing.</small>

                        <?php if($dressType->diagram_front): ?>
                            <div class="mt-2">
                                <div class="text-muted small mb-1">Current Front Image:</div>
                                <img src="<?php echo e(asset($dressType->diagram_front)); ?>" class="img-thumbnail"
                                    style="max-height:150px;">
                            </div>

                            
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="remove_diagram_front" value="1"
                                    id="remove_front">
                                <label class="form-check-label" for="remove_front">Remove front image</label>
                            </div>
                        <?php endif; ?>
                    </div>

                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Diagram Back (optional)</label>
                        <input type="file" name="diagram_back" class="form-control" accept="image/*">
                        <small class="text-muted">Upload new image to replace existing.</small>

                        <?php if($dressType->diagram_back): ?>
                            <div class="mt-2">
                                <div class="text-muted small mb-1">Current Back Image:</div>
                                <img src="<?php echo e(asset($dressType->diagram_back)); ?>" class="img-thumbnail"
                                    style="max-height:150px;">
                            </div>

                            
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="remove_diagram_back" value="1"
                                    id="remove_back">
                                <label class="form-check-label" for="remove_back">Remove back image</label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"><?php echo e($dressType->notes); ?></textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                            <?php echo e($dressType->is_active ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo e(route('dress-types.index')); ?>" class="btn btn-secondary">Back</a>
                    <button class="btn btn-primary" type="submit">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('editDressTypeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            fetch(this.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                    "Accept": "application/json"
                }
            }).then(async res => {
                let data = await res.json().catch(() => ({}));
                const msg = document.getElementById('message');

                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
                        msg.innerHTML = `<div class="alert alert-danger">${Object.values(data.errors).flat().join('<br>')}</div>`;
                        return;
                    }
                    msg.innerHTML = `<div class="alert alert-danger">${data.message || 'Something went wrong.'}</div>`;
                    return;
                }

                msg.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                setTimeout(() => window.location.href = "<?php echo e(route('dress-types.index')); ?>", 1200);
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Dress Type Edit'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/dress_types/edit.blade.php ENDPATH**/ ?>