

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Workflow Stages', 'subtitle' => 'Create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">New Workflow Stage</h5>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <form id="createStageForm" action="<?php echo e(route('workflow-stages.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control" placeholder="Ex: CUT" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Ex: Cutting" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0" min="0">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo e(route('workflow-stages.index')); ?>" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Create Stage</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('createStageForm').addEventListener('submit', function(e) {
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
                        document.getElementById('message').innerHTML =
                            `<div class="alert alert-danger">${errors}</div>`;
                        return;
                    }
                    document.getElementById('message').innerHTML =
                        `<div class="alert alert-danger">Something went wrong.</div>`;
                    return;
                }

                document.getElementById('message').innerHTML =
                    `<div class="alert alert-success">${data.message}</div>`;

                this.reset();
                setTimeout(() => document.getElementById('message').innerHTML = "", 2500);
            }).catch(err => {
                document.getElementById('message').innerHTML =
                    `<div class="alert alert-danger">Error: ${err}</div>`;
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Workflow Stage Create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/workflow_stages/create.blade.php ENDPATH**/ ?>