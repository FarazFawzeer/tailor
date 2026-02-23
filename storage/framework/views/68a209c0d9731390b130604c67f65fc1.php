

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Tailoring', 'subtitle' => 'Production Dashboard'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <div class="row">
        <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $count = (int)($stageCounts[$s->id] ?? 0);
                $qty   = (int)($stageQtyCounts[$s->id] ?? 0);
                $isLast = $lastStage && $lastStage->id === $s->id;
            ?>

            <div class="col-md-6 col-xl-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1"><?php echo e($s->name); ?></p>
                                <h3 class="mb-0"><?php echo e($count); ?></h3>
                                <small class="text-muted">Total Qty: <?php echo e($qty); ?></small>
                            </div>
                            <div class="avatar-md bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <iconify-icon icon="<?php echo e($isLast ? 'solar:box-outline' : 'solar:scissors-outline'); ?>"
                                    class="fs-32 text-primary"></iconify-icon>
                            </div>
                        </div>

                        <?php if($isLast): ?>
                            <div class="mt-2">
                                <span class="badge bg-success">Ready for Delivery Stage</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="row">
        
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Ready for Delivery Items</span>
                        <b><?php echo e($readyForDeliveryCount); ?></b>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Ready for Delivery Qty</span>
                        <b><?php echo e($readyForDeliveryQty); ?></b>
                    </div>

                    <?php if($hasDueDate): ?>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="text-danger">Overdue Items</span>
                            <b class="text-danger"><?php echo e($overdueCount); ?></b>
                        </div>
                        <small class="text-muted">Based on jobs.due_date</small>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            Overdue tracking disabled (jobs.due_date column not found).
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Staff Workload (Optional)</h5>
                    <p class="card-subtitle mb-0">Shows only if handover_logs table exists.</p>
                </div>
                <div class="card-body">
                    <?php if($staffWorkload->count()): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Staff</th>
                                        <th>Stage</th>
                                        <th>Handovers</th>
                                        <th>Total Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $staffWorkload; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($w->staff_name ?? 'N/A'); ?></td>
                                            <td><?php echo e($w->stage_name ?? 'N/A'); ?></td>
                                            <td><?php echo e($w->handovers); ?></td>
                                            <td><?php echo e($w->total_qty ?? 0); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary mb-0">
                            Workload data not available yet (handover logs not created).
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <?php if($hasDueDate && $overdueItems->count()): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0 text-danger">Overdue Items (Top 10)</h5>
            </div>
            <div class="card-body">
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
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $overdueItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($o->job_no); ?></td>
                                    <td><?php echo e($o->batch_no); ?></td>
                                  <td><?php echo e($o->full_name ?? 'N/A'); ?></td>
                                    <td><?php echo e($o->dress_name ?? 'N/A'); ?></td>
                                    <td><?php echo e($o->qty); ?></td>
                                    <td><span class="badge bg-warning"><?php echo e($o->stage_name ?? 'N/A'); ?></span></td>
                                    <td class="text-danger"><?php echo e(\Carbon\Carbon::parse($o->due_date)->format('d M Y')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Latest Production Items</h5>
            <p class="card-subtitle mb-0">Recently updated items (by updated_at).</p>
        </div>

        <div class="card-body">
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
                            <th>Due</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $latestItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($it->job_no); ?></td>
                                <td><?php echo e($it->batch_no); ?></td>
                           <td><?php echo e($it->full_name ?? 'N/A'); ?></td>
                                <td><?php echo e($it->dress_name ?? 'N/A'); ?></td>
                                <td><?php echo e($it->qty); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo e($it->stage_name ?? 'N/A'); ?></span>
                                </td>
                                <td>
                                    <?php if(!empty($it->due_date)): ?>
                                        <?php echo e(\Carbon\Carbon::parse($it->due_date)->format('d M Y')); ?>

                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e(\Carbon\Carbon::parse($it->updated_at)->format('d M Y, h:i A')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No production items found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Production Dashboard'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/tailoring/dashboard/production.blade.php ENDPATH**/ ?>