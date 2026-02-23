

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Dress Types', 'subtitle' => 'View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title mb-0">Dress Type List</h5>
                <p class="card-subtitle">All dress types with diagrams and status.</p>
            </div>

            <a href="<?php echo e(route('dress-types.create')); ?>" class="btn btn-primary">+ Create</a>
        </div>

        <div class="card-body">
            <div id="message"></div>

            
            <form method="GET" action="<?php echo e(route('dress-types.index')); ?>" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" value="<?php echo e($q ?? ''); ?>"
                        placeholder="Search by code or name">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-outline-secondary w-100" href="<?php echo e(route('dress-types.index')); ?>">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Front</th>
                            <th>Back</th>
                            <th>Status</th>
                            <th>Updated At</th>
                            <th style="width: 220px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $dressTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr id="dressType-<?php echo e($dt->id); ?>">
                                <td><b><?php echo e($dt->code); ?></b></td>
                                <td><?php echo e($dt->name); ?></td>

                                
                                <td>
                                    <?php if($dt->diagram_front): ?>
                                        <a href="<?php echo e(asset($dt->diagram_front)); ?>" target="_blank">
                                            <img src="<?php echo e(asset($dt->diagram_front)); ?>"
                                                alt="Front"
                                                class="rounded border"
                                                style="width: 60px; height: 60px; object-fit: cover;">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                
                                <td>
                                    <?php if($dt->diagram_back): ?>
                                        <a href="<?php echo e(asset($dt->diagram_back)); ?>" target="_blank">
                                            <img src="<?php echo e(asset($dt->diagram_back)); ?>"
                                                alt="Back"
                                                class="rounded border"
                                                style="width: 60px; height: 60px; object-fit: cover;">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if($dt->is_active): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>

                                <td><?php echo e($dt->updated_at?->format('d M Y, h:i A') ?? '-'); ?></td>

                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="<?php echo e(route('dress-types.edit', $dt)); ?>" class="btn btn-info btn-sm w-100">
                                            Edit
                                        </a>

                                        <button type="button" class="btn btn-danger btn-sm w-100 delete-dressType"
                                            data-id="<?php echo e($dt->id); ?>">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No dress types found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                
                <div class="d-flex justify-content-end mt-3">
                    <?php echo e($dressTypes->links()); ?>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.delete-dressType').forEach(button => {
            button.addEventListener('click', function() {
                let id = this.dataset.id;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This dress type will be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    fetch("<?php echo e(url('dress-types')); ?>/" + id, {
                            method: "DELETE",
                            headers: {
                                "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>",
                                "Accept": "application/json"
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('dressType-' + id)?.remove();
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
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Dress Types View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/dress_types/index.blade.php ENDPATH**/ ?>