

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Workflow Stages', 'subtitle' => 'View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title mb-0">Workflow Stages</h5>
                <p class="card-subtitle">Manage stage order (Cut → Sewing → Button → Ironing → Packaging).</p>
            </div>
            <a href="<?php echo e(route('workflow-stages.create')); ?>" class="btn btn-primary">+ Add</a>
        </div>

        <div class="card-body">

            <form method="GET" action="<?php echo e(route('workflow-stages.index')); ?>" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" value="<?php echo e(request('q')); ?>"
                        placeholder="Search by Code / Name">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-outline-secondary w-100" href="<?php echo e(route('workflow-stages.index')); ?>">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered">
                    <thead class="table-light">
                        <tr>
                            <th>Sort</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Updated At</th>
                            <th style="width: 230px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr id="stage-<?php echo e($s->id); ?>">
                                <td><?php echo e($s->sort_order); ?></td>
                                <td><?php echo e($s->code); ?></td>
                                <td><?php echo e($s->name); ?></td>
                                <td>
                                    <?php if($s->is_active): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($s->updated_at->format('d M Y, h:i A')); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-warning btn-sm w-100" href="<?php echo e(route('workflow-stages.edit', $s)); ?>">Edit</a>
                                        <button type="button" class="btn btn-danger btn-sm w-100 delete-stage" data-id="<?php echo e($s->id); ?>">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="text-center text-muted">No workflow stages found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    <?php echo e($stages->links()); ?>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.delete-stage').forEach(btn => {
            btn.addEventListener('click', function() {
                let id = this.dataset.id;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This workflow stage will be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    fetch("<?php echo e(url('workflow-stages')); ?>/" + id, {
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
                            document.getElementById('stage-' + id)?.remove();
                            Swal.fire('Deleted!', data.message, 'success');
                        } else {
                            Swal.fire('Error!', data.message || 'Something went wrong!', 'error');
                        }
                    }).catch(() => {
                        Swal.fire('Error!', 'Something went wrong!', 'error');
                    });
                });
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Workflow Stages View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/workflow_stages/index.blade.php ENDPATH**/ ?>