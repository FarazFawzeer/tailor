

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Dress Types', 'subtitle' => 'Create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">New Dress Type</h5>
        </div>

        <div class="card-body">
            <div id="message"></div>

<form id="createDressTypeForm" action="<?php echo e(route('dress-types.store')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Code</label>
                        <input name="code" class="form-control" placeholder="Ex: SHIRT" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name</label>
                        <input name="name" class="form-control" placeholder="Ex: Shirt" required>
                    </div>
                </div>

                <div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Diagram Front (optional)</label>
        <input type="file" name="diagram_front" class="form-control" accept="image/*">
        <small class="text-muted">Upload shirt front / trouser front image (PNG/SVG/JPG)</small>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Diagram Back (optional)</label>
        <input type="file" name="diagram_back" class="form-control" accept="image/*">
        <small class="text-muted">Upload back view image if needed</small>
    </div>
</div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo e(route('dress-types.index')); ?>" class="btn btn-secondary">Back</a>
                    <button class="btn btn-primary" type="submit">Create</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('createDressTypeForm').addEventListener('submit', function(e) {
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
                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
                        let errors = Object.values(data.errors).flat().join('<br>');
                        document.getElementById('message').innerHTML = `<div class="alert alert-danger">${errors}</div>`;
                        return;
                    }
                    document.getElementById('message').innerHTML = `<div class="alert alert-danger">Something went wrong.</div>`;
                    return;
                }

                document.getElementById('message').innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                this.reset();
                setTimeout(() => document.getElementById('message').innerHTML = "", 3000);
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Dress Type Create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/dress_types/create.blade.php ENDPATH**/ ?>