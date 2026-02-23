

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Hiring', 'subtitle' => 'Agreements'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Hire Agreements</h5>
            <p class="card-subtitle mb-0">Issue / Return agreements and track fine.</p>
        </div>

        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input name="q" class="form-control" value="<?php echo e($q ?? ''); ?>" placeholder="Search agreement no / customer name">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <?php $__currentLoopData = ['issued','returned','cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($st); ?>" <?php echo e(($status ?? '') === $st ? 'selected' : ''); ?>><?php echo e(ucfirst($st)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <a href="<?php echo e(route('hiring.agreements.create')); ?>" class="btn btn-primary w-100">Create</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Agreement</th>
                            <th>Customer</th>
                            <th>Issue</th>
                            <th>Expected Return</th>
                            <th>Status</th>
                            <th>Hire Total</th>
                            <th>Fine</th>
                            <th width="160">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $agreements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><b><?php echo e($a->agreement_no); ?></b></td>
                                <td><?php echo e($a->customer?->full_name ?? 'N/A'); ?></td>
                                <td><?php echo e($a->issue_date?->format('d M Y')); ?></td>
                                <td><?php echo e($a->expected_return_date?->format('d M Y')); ?></td>
                                <td>
                                    <?php if($a->status === 'issued'): ?>
                                        <span class="badge bg-warning">Issued</span>
                                    <?php elseif($a->status === 'returned'): ?>
                                        <span class="badge bg-success">Returned</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e(number_format((float)$a->total_hire_amount, 2)); ?></td>
                                <td><?php echo e(number_format((float)$a->fine_amount, 2)); ?></td>
                                <td>
                                    <a href="<?php echo e(route('hiring.agreements.show', $a)); ?>" class="btn btn-outline-dark btn-sm w-100">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="8" class="text-center text-muted">No agreements found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <?php echo e($agreements->links()); ?>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Hire Agreements'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/hiring/agreements/index.blade.php ENDPATH**/ ?>