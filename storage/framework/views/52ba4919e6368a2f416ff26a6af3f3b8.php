

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Hiring', 'subtitle' => 'Agreement Details'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><?php echo e($agreement->agreement_no); ?></h5>
            <p class="card-subtitle mb-0">
                Customer: <b><?php echo e($agreement->customer?->full_name); ?></b> |
                Status:
                <?php if($agreement->status === 'issued'): ?>
                    <span class="badge bg-warning">Issued</span>
                <?php elseif($agreement->status === 'returned'): ?>
                    <span class="badge bg-success">Returned</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Cancelled</span>
                <?php endif; ?>
            </p>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3"><b>Issue:</b> <?php echo e($agreement->issue_date?->format('d M Y')); ?></div>
                <div class="col-md-3"><b>Expected Return:</b> <?php echo e($agreement->expected_return_date?->format('d M Y')); ?></div>
                <div class="col-md-3"><b>Actual Return:</b> <?php echo e($agreement->actual_return_date?->format('d M Y') ?? '-'); ?></div>
                <div class="col-md-3"><b>Fine:</b> Rs <?php echo e(number_format((float)$agreement->fine_amount,2)); ?></div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Code</th>
                            <th>Hire Price</th>
                            <th>Deposit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $agreement->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ai): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $thumb = $ai->item?->images?->first()?->image_path ? asset($ai->item->images->first()->image_path) : asset('/images/users/avatar-6.jpg');
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img class="rounded" src="<?php echo e($thumb); ?>" style="width:40px;height:40px;object-fit:cover;">
                                        <div>
                                            <div class="fw-bold"><?php echo e($ai->item?->name); ?></div>
                                            <div class="text-muted small"><?php echo e($ai->item?->category ?? ''); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><b><?php echo e($ai->item?->item_code); ?></b></td>
                                <td>Rs <?php echo e(number_format((float)$ai->hire_price,2)); ?></td>
                                <td>Rs <?php echo e(number_format((float)$ai->deposit_amount,2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?php echo e(route('hiring.agreements.index')); ?>" class="btn btn-secondary">Back</a>

                <?php if($agreement->status === 'issued'): ?>
                    <a href="<?php echo e(route('hiring.agreements.return', $agreement)); ?>" class="btn btn-success">
                        Return Items
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Agreement View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/hiring/agreements/show.blade.php ENDPATH**/ ?>