

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Admin', 'subtitle' => 'View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Admin List</h5>
            <p class="card-subtitle">All admins in your system with details.</p>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <div class="table-responsive">
                    <table class="table table-hover table-centered">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Role</th>
                                <th scope="col">Updated At</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr id="user-<?php echo e($user->id); ?>">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?php echo e($user->image_path ? asset($user->image_path) : asset('/images/users/avatar-6.jpg')); ?>"
                                                alt="<?php echo e($user->name); ?>" class="avatar-sm rounded-circle">
                                            <div>
                                                <h6 class="mb-0"><?php echo e($user->name); ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo e($user->email); ?></td>
                                    <td><?php echo e(ucfirst($user->type)); ?></td>
                                    <td><?php echo e($user->updated_at->format('d M Y, h:i A')); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm w-100 delete-user"
                                            data-id="<?php echo e($user->id); ?>">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>

                    
                    <div class="d-flex justify-content-end mt-3">
                        <?php echo e($users->links()); ?>

                    </div>
                </div>


            </div>
        </div>
    </div>


    <script>
        document.querySelectorAll('.delete-user').forEach(button => {
            button.addEventListener('click', function() {
                let userId = this.dataset.id;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("<?php echo e(url('admin/users')); ?>/" + userId, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': "<?php echo e(csrf_token()); ?>",
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Remove the deleted row
                                    document.getElementById('user-' + userId).remove();

                                    Swal.fire(
                                        'Deleted!',
                                        data.message,
                                        'success'
                                    );
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        data.message || 'Something went wrong!',
                                        'error'
                                    );
                                }
                            })
                            .catch(error => {
                                Swal.fire(
                                    'Error!',
                                    'Something went wrong!',
                                    'error'
                                );
                            });
                    }
                });
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Admin View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/admin/users.blade.php ENDPATH**/ ?>