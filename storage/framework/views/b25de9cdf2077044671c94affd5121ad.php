

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Tailoring', 'subtitle' => 'Handover'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Handover Items</h5>
            <p class="card-subtitle mb-0">
                Move items to next stage and track logs.
                <span class="text-muted">Partial handover is supported (Ex: send 2 now, 3 later).</span>
            </p>
        </div>

        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input name="q" class="form-control" value="<?php echo e($q ?? ''); ?>"
                        placeholder="Search Job No / Batch No">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <a href="<?php echo e(route('tailoring.handover.index')); ?>" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Job</th>
                            <th>Batch</th>
                            <th>Customer</th>
                            <th>Dress</th>
                            <th>Qty</th>
                            <th>Stage</th>
                            <th>Group</th>
                            <th>Completed</th>
                            <th width="260">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $groupId = $it->parent_item_id ? $it->parent_item_id : $it->id;
                            ?>

                            <tr>
                                <td><?php echo e($it->jobBatch?->job?->job_no ?? '-'); ?></td>
                                <td><?php echo e($it->jobBatch?->batch_no ?? '-'); ?></td>
                                <td><?php echo e($it->jobBatch?->job?->customer?->full_name ?? 'N/A'); ?></td>
                                <td><?php echo e($it->dressType?->name ?? 'N/A'); ?></td>

                                <td>
                                    <b><?php echo e($it->qty); ?></b>
                                    <?php if($it->parent_item_id): ?>
                                        <span class="badge bg-warning ms-1">Part</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="badge bg-info"><?php echo e($it->stage?->name ?? 'N/A'); ?></span>
                                </td>

                                <td>
                                    <span class="text-muted">#<?php echo e($groupId); ?></span>
                                </td>

                                <td>
                                    <?php if($it->completed_at): ?>
                                        <span class="badge bg-success">Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php
                                        $groupKey = $it->parent_item_id ?: $it->id;
                                    ?>

                                    <div class="d-flex flex-column gap-2">
                                        <a href="<?php echo e(route('tailoring.handover.group.create', $groupKey)); ?>"
                                            class="btn btn-primary btn-sm w-100">
                                            Group Handover
                                        </a>

                                        <div class="d-flex gap-2">
                                            <a href="<?php echo e(route('tailoring.handover.history', $it)); ?>"
                                                class="btn btn-outline-dark btn-sm w-100">
                                                History
                                            </a>

                                            <?php if(!$it->completed_at): ?>
                                                <a href="<?php echo e(route('tailoring.handover.create', $it)); ?>"
                                                    class="btn btn-outline-primary btn-sm w-100">
                                                    Single
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-secondary btn-sm w-100" disabled>
                                                    Single
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">No items found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    <?php echo e($items->links()); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Handover'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/tailoring/handover/index.blade.php ENDPATH**/ ?>