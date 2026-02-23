

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Measurement Templates', 'subtitle' => 'View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title mb-0">Measurement Templates</h5>
                <p class="card-subtitle">Templates for each dress type (fields inside template).</p>
            </div>
            <a href="<?php echo e(route('measurement-templates.create')); ?>" class="btn btn-primary">+ Add</a>
        </div>

        <div class="card-body">
            <form method="GET" action="<?php echo e(route('measurement-templates.index')); ?>" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" value="<?php echo e(request('q')); ?>"
                        placeholder="Search by Template Name / Dress Type">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-outline-secondary w-100" href="<?php echo e(route('measurement-templates.index')); ?>">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered">
                    <thead class="table-light">
                        <tr>
                            <th>Dress Type</th>
                            <th>Template Name</th>
                            <th>Status</th>
                            <th>Updated At</th>
                            <th style="width: 230px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr id="template-<?php echo e($t->id); ?>">
                                <td><?php echo e($t->dressType?->name); ?> (<?php echo e($t->dressType?->code); ?>)</td>
                                <td><?php echo e($t->name); ?></td>
                                <td>
                                    <?php if($t->is_active): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($t->updated_at->format('d M Y, h:i A')); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-warning btn-sm w-100"
                                            href="<?php echo e(route('measurement-templates.edit', $t)); ?>">Edit</a>
                                        <button class="btn btn-danger btn-sm w-100 delete-template"
                                            data-id="<?php echo e($t->id); ?>">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5" class="text-center text-muted">No templates found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    <?php echo e($templates->links()); ?>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.delete-template').forEach(btn => {
            btn.addEventListener('click', function() {
                let id = this.dataset.id;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This template and its fields will be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    fetch("<?php echo e(url('measurement-templates')); ?>/" + id, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>",
                            "Accept": "application/json"
                        }
                    }).then(async res => {
                        let data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            Swal.fire('Error!', data.message || 'Something went wrong!', 'error');
                            return;
                        }
                        if (data.success) {
                            document.getElementById('template-' + id)?.remove();
                            Swal.fire('Deleted!', data.message, 'success');
                        } else {
                            Swal.fire('Error!', data.message || 'Something went wrong!', 'error');
                        }
                    });
                });
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Measurement Templates View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/measurement_templates/index.blade.php ENDPATH**/ ?>