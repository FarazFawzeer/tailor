

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Tailoring', 'subtitle' => 'Delivery'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Delivery Jobs</h5>
            <p class="card-subtitle mb-0">View invoice and mark jobs as delivered.</p>
        </div>

        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input name="q" class="form-control" value="<?php echo e($q ?? ''); ?>" placeholder="Search Job No / Customer">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <a href="<?php echo e(route('tailoring.delivery.index')); ?>" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Job No</th>
                            <th>Customer</th>
                            <th>Delivered</th>
                            <th>Delivered Date</th>
                            <th>Grand Total</th>
                            <th width="260">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><b><?php echo e($j->job_no); ?></b></td>
                                <td><?php echo e($j->customer?->full_name ?? 'N/A'); ?></td>
                                <td>
                                    <?php if($j->delivery): ?>
                                        <span class="badge bg-success">Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($j->delivery?->delivered_date?->format('d M Y') ?? '-'); ?></td>
                                <td><?php echo e(number_format((float)($j->delivery?->grand_total ?? 0), 2)); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-outline-dark btn-sm w-100"
                                           href="<?php echo e(route('tailoring.delivery.invoice', $j)); ?>">Invoice</a>
                                        <a class="btn btn-info btn-sm w-100"
                                           href="<?php echo e(route('tailoring.delivery.print', $j)); ?>" target="_blank">Print</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No jobs found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    <?php echo e($jobs->links()); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Delivery'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/tailoring/delivery/index.blade.php ENDPATH**/ ?>