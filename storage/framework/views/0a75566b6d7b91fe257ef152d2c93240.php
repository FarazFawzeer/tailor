

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Hiring Inventory', 'subtitle' => 'View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Hire Items</h5>
            <p class="card-subtitle mb-0">Manage hire dress inventory with unique codes and images.</p>
        </div>

        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input name="q" class="form-control" value="<?php echo e($q ?? ''); ?>" placeholder="Search code / name / category">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <?php $__currentLoopData = ['available','reserved','hired','maintenance']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($st); ?>" <?php echo e(($status ?? '') === $st ? 'selected' : ''); ?>>
                                <?php echo e(ucfirst($st)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-outline-dark w-100">Go</button>
                </div>
                <div class="col-md-2">
                    <a href="<?php echo e(route('hiring.items.create')); ?>" class="btn btn-primary w-100">Create</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Code</th>
                            <th>Category</th>
                            <th>Size/Color</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th width="220">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr id="row-<?php echo e($it->id); ?>">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img class="avatar-sm rounded"
                                             src="<?php echo e($it->images->first()?->image_path ? asset($it->images->first()->image_path) : asset('/images/users/avatar-6.jpg')); ?>"
                                             alt="img">
                                        <div>
                                            <div class="fw-bold"><?php echo e($it->name); ?></div>
                                            <div class="text-muted small"><?php echo e($it->notes ? \Illuminate\Support\Str::limit($it->notes, 40) : ''); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><b><?php echo e($it->item_code); ?></b></td>
                                <td><?php echo e($it->category ?? '-'); ?></td>
                                <td><?php echo e($it->size ?? '-'); ?> / <?php echo e($it->color ?? '-'); ?></td>
                                <td><?php echo e(number_format((float)$it->hire_price, 2)); ?></td>
                                <td><span class="badge bg-info"><?php echo e(ucfirst($it->status)); ?></span></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="<?php echo e(route('hiring.items.edit', $it)); ?>" class="btn btn-outline-dark btn-sm w-100">Edit</a>
                                        <button class="btn btn-danger btn-sm w-100 btn-delete" data-id="<?php echo e($it->id); ?>">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="7" class="text-center text-muted">No items found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <?php echo e($items->links()); ?>

            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;

                Swal.fire({
                    title: 'Delete this item?',
                    text: "You can't undo this.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete'
                }).then((r) => {
                    if (!r.isConfirmed) return;

                    fetch("<?php echo e(url('hiring/items')); ?>/" + id, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>",
                            "Accept": "application/json"
                        }
                    }).then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            Swal.fire('Error', data.message || 'Something went wrong', 'error');
                            return;
                        }
                        document.getElementById('row-' + id)?.remove();
                        Swal.fire('Deleted', data.message, 'success');
                    });
                });
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Hire Items'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/hiring/items/index.blade.php ENDPATH**/ ?>