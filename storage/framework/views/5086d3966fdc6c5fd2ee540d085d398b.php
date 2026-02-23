

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Staff', 'subtitle' => 'View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title mb-0">Staff List</h5>
                <p class="card-subtitle">All staff in your system with details.</p>
            </div>

            <a href="<?php echo e(route('staff.create')); ?>" class="btn btn-primary">
                + Add Staff
            </a>
        </div>

        <div class="card-body">

            <form method="GET" action="<?php echo e(route('staff.index')); ?>" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" value="<?php echo e(request('q')); ?>"
                        placeholder="Search by Name / Email">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100" type="submit">Search</button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-outline-secondary w-100" href="<?php echo e(route('staff.index')); ?>">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Staff Code</th>
                            <th>Status</th>
                            <th>Updated At</th>
                            <th style="width: 210px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $role = optional($u->roles->first())->name;
                            ?>
                            <tr id="staff-<?php echo e($u->id); ?>">
                                <td><?php echo e($u->name); ?></td>
                                <td><?php echo e($u->email); ?></td>
                                <td><?php echo e($role ? ucwords(str_replace('_', ' ', $role)) : '-'); ?></td>
                                <td><?php echo e($u->staffProfile?->staff_code ?? '-'); ?></td>
                                <td>
                                    <?php if($u->staffProfile?->is_active): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e(optional($u->updated_at)->format('d M Y, h:i A')); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="<?php echo e(route('staff.edit', $u)); ?>" class="btn btn-warning btn-sm w-100">
                                            Edit
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm w-100 delete-staff"
                                            data-id="<?php echo e($u->id); ?>">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No staff found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    <?php echo e($staff->links()); ?>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.delete-staff').forEach(button => {
            button.addEventListener('click', function() {
                let staffId = this.dataset.id;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This staff account will be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    fetch("<?php echo e(url('staff')); ?>/" + staffId, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': "<?php echo e(csrf_token()); ?>",
                                'Accept': 'application/json'
                            }
                        })
                        .then(async response => {
                            const data = await response.json().catch(() => ({}));

                            if (!response.ok) {
                                Swal.fire('Error!', data.message || 'Something went wrong!', 'error');
                                return;
                            }

                            if (data.success) {
                                document.getElementById('staff-' + staffId)?.remove();
                                Swal.fire('Deleted!', data.message, 'success');
                            } else {
                                Swal.fire('Error!', data.message || 'Something went wrong!', 'error');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error!', 'Something went wrong!', 'error');
                        });
                });
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Staff View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/staff/index.blade.php ENDPATH**/ ?>