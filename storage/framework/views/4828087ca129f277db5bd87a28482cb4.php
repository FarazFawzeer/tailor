

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Hiring Inventory', 'subtitle' => 'Create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">New Hire Item</h5>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <form id="createHireItemForm" action="<?php echo e(route('hiring.items.store')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Unique Code</label>
                        <input name="item_code" class="form-control" placeholder="Ex: HIRE-SUIT-0001" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Name</label>
                        <input name="name" class="form-control" placeholder="Ex: White Suit" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Category</label>
                        <input name="category" class="form-control" placeholder="Ex: Suit / Shirt / Kurtha">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Size</label>
                        <input name="size" class="form-control" placeholder="Ex: L / 42">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Color</label>
                        <input name="color" class="form-control" placeholder="Ex: White">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Hire Price</label>
                        <input name="hire_price" type="number" step="0.01" class="form-control" value="0" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Deposit (optional)</label>
                        <input name="deposit_amount" type="number" step="0.01" class="form-control" value="0">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="available">Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="hired">Hired</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label">Images (optional)</label>
                        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted">You can upload multiple images.</small>
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
                    <a href="<?php echo e(route('hiring.items.index')); ?>" class="btn btn-secondary">Back</a>
                    <button class="btn btn-primary" type="submit">Create Item</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('createHireItemForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const fd = new FormData(form);

            fetch(form.action, {
                method: "POST",
                body: fd,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                    "Accept": "application/json"
                }
            }).then(async res => {
                const data = await res.json().catch(() => ({}));
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
                form.reset();
                setTimeout(() => msg.innerHTML = "", 3000);
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Hire Item Create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/hiring/items/create.blade.php ENDPATH**/ ?>