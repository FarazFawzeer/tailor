

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Tailoring', 'subtitle' => 'Work Queue'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">My Work Queue</h5>
            <p class="card-subtitle mb-0">
                This page shows <b>grouped pending qty</b> for your stage. Use <b>Group Handover</b> to move remaining qty
                without affecting other stages.
            </p>
        </div>

        <div class="card-body">
            
            <form method="GET" action="<?php echo e(route('tailoring.workqueue.index')); ?>" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" name="q" class="form-control" value="<?php echo e($q ?? ''); ?>"
                        placeholder="Search Job No / Batch No / Customer / Dress">
                </div>

                <?php if($canViewAll): ?>
                    <div class="col-md-3">
                        <select name="stage_id" class="form-select">
                            <option value="">All Stages</option>
                            <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($s->id); ?>" <?php echo e((string)$selectedStageId === (string)$s->id ? 'selected' : ''); ?>>
                                    <?php echo e($s->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>

                <div class="col-md-2">
                    <a href="<?php echo e(route('tailoring.workqueue.index')); ?>" class="btn btn-outline-secondary w-100">Reset</a>
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
                            <th>Stage</th>
                            <th>Available Qty</th>
                            <th>Last Updated</th>
                            <th style="width: 320px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><b><?php echo e($it->job_no ?? '-'); ?></b></td>
                                <td><?php echo e($it->batch_no ?? '-'); ?></td>
                                <td><?php echo e($it->customer_name ?? 'N/A'); ?></td>
                                <td><?php echo e($it->dress_name ?? 'N/A'); ?></td>

                                <td>
                                    <span class="badge bg-info"><?php echo e($it->stage_name ?? 'N/A'); ?></span>
                                    <div class="small text-muted">Group #<?php echo e($it->group_id); ?></div>
                                </td>

                                <td>
                                    <span class="fs-5 fw-bold"><?php echo e($it->total_qty); ?></span>
                                    <div class="small text-muted">Qty you can move now</div>
                                </td>

                                <td>
                                    <?php echo e(\Carbon\Carbon::parse($it->last_updated_at)->format('d M Y, h:i A')); ?>

                                </td>

                                <td>
                                    <div class="d-flex gap-2">
                                        
                                        <a href="<?php echo e(route('tailoring.handover.group.create', $it->group_id)); ?>"
                                           class="btn btn-primary btn-sm w-100">
                                            Group Handover
                                        </a>

                                        
                                        <a href="<?php echo e(route('tailoring.handover.index', ['q' => $it->job_no])); ?>"
                                           class="btn btn-outline-dark btn-sm w-100">
                                            View in Handover
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    No items in your queue.
                                </td>
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
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Work Queue'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/tailoring/work_queue/index.blade.php ENDPATH**/ ?>