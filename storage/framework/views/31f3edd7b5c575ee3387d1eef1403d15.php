

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Handover', 'subtitle' => 'History'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $jobNo   = $item->jobBatch?->job?->job_no ?? '-';
        $batchNo = $item->jobBatch?->batch_no ?? '-';
        $customer = $item->jobBatch?->job?->customer?->full_name ?? 'N/A';
        $dress = $item->dressType?->name ?? 'N/A';

        $groupId = $item->parent_item_id ? $item->parent_item_id : $item->id;
        $isPartial = (bool)$item->parent_item_id;
    ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-1">
                Job: <?php echo e($jobNo); ?> | Batch: <?php echo e($batchNo); ?>

                <?php if($isPartial): ?>
                    <span class="badge bg-warning ms-2">Partial Item</span>
                <?php endif; ?>
            </h5>

            <p class="card-subtitle mb-0">
                Customer: <b><?php echo e($customer); ?></b> |
                Dress: <b><?php echo e($dress); ?></b> |
                Item Qty: <b><?php echo e($item->qty); ?></b>
                <span class="text-muted">| Group: #<?php echo e($groupId); ?></span>
            </p>
        </div>

        <div class="card-body">
            <?php if($item->completed_at): ?>
                <div class="alert alert-success">
                    ✅ Completed on <b><?php echo e($item->completed_at?->format('d M Y, h:i A')); ?></b>
                </div>
            <?php else: ?>
                <div class="alert alert-secondary">
                    ⏳ Not completed yet. Current Stage: <b><?php echo e($item->stage?->name ?? 'N/A'); ?></b>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>From Stage</th>
                            <th>To Stage</th>
                            <th>Qty Moved</th>
                            <th>Handed By</th>
                            <th>Received By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($l->handover_at?->format('d M Y, h:i A') ?? '-'); ?></td>
                                <td><?php echo e($l->fromStage?->name ?? '-'); ?></td>
                                <td>
                                    <?php if($l->to_stage_id): ?>
                                        <span class="badge bg-info"><?php echo e($l->toStage?->name ?? '-'); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Completed</span>
                                    <?php endif; ?>
                                </td>
                                <td><b><?php echo e($l->qty); ?></b></td>
                                <td><?php echo e($l->handedBy?->name ?? '-'); ?></td>
                                <td><?php echo e($l->receivedBy?->name ?? '-'); ?></td>
                                <td class="text-muted"><?php echo e($l->notes ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No handover records.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                <a href="<?php echo e(route('tailoring.handover.index')); ?>" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Handover History'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/tailoring/handover/history.blade.php ENDPATH**/ ?>