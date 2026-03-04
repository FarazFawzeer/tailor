

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Tailoring', 'subtitle' => 'Production Dashboard'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <style>
        .stat-card {
            border: 1px solid rgba(0, 0, 0, .07);
            border-radius: 14px;
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .muted {
            font-size: 12px;
            color: #6c757d;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }
    </style>

    <style>
        .stage-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .stage-card {
            flex: 0 0 calc(20% - 12px);
            /* 5 cards per row */
        }

        @media (max-width:1200px) {
            .stage-card {
                flex: 0 0 calc(33.33% - 12px);
            }

            /* tablet */
        }

        @media (max-width:768px) {
            .stage-card {
                flex: 0 0 calc(50% - 12px);
            }

            /* mobile */
        }

        @media (max-width:500px) {
            .stage-card {
                flex: 0 0 100%;
            }
        }
    </style>


    <div class="stage-row">

        <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $count = (int) ($stageCounts[$s->id] ?? 0);
                $qty = (int) ($stageQtyCounts[$s->id] ?? 0);
                $isLast = $lastStage && $lastStage->id === $s->id;
            ?>

            <div class="stage-card">
                <div class="card stat-card h-100">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>
                                <div class="text-muted"><?php echo e($s->name); ?></div>

                                <div class="d-flex align-items-end gap-2">
                                    <h2 class="mb-0"><?php echo e($count); ?></h2>
                                    <span class="muted mb-1">items</span>
                                </div>

                                <div class="muted">Total Qty: <b><?php echo e($qty); ?></b></div>

                                <?php if($isLast): ?>
                                    <div class="mt-2">
                                        <span class="badge bg-success">Ready for Delivery</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="stat-icon bg-primary bg-opacity-10">
                                <iconify-icon icon="<?php echo e($isLast ? 'solar:box-outline' : 'solar:scissors-outline'); ?>"
                                    class="fs-28 text-primary">
                                </iconify-icon>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>


    <div class="row mt-3">

        
        <?php if($hasDueDate && $overdueItems->count()): ?>
            <div class="col-lg-4 mb-3">
                <div class="card stat-card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-danger">Overdue </h5>
                        <span class="muted">Most urgent</span>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Job</th>
                                        <th>Customer</th>


                                        <th>Due</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php $__currentLoopData = $overdueItems->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($o->job_no); ?></td>
                                            <td><?php echo e($o->full_name ?? 'N/A'); ?></td>


                                            <td class="text-danger">
                                                <?php echo e(\Carbon\Carbon::parse($o->due_date)->format('d M Y')); ?>

                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>


        
        <div class="col-lg-8 mb-3">
            <div class="card stat-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Latest Items</h5>
                        <div class="muted">Last updated </div>
                    </div>

                    <a href="<?php echo e(route('tailoring.jobs.index')); ?>" class="btn btn-outline-primary btn-sm">
                        View Jobs
                    </a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">

                            <thead class="table-light">
                                <tr>
                                    <th>Job</th>
                                    <th>Customer</th>
                                    <th>Dress</th>
                                    <th class="text-end">Qty</th>
                                    <th>Stage</th>
                                    <th>Due</th>
                                    <th>Updated</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $latestItems->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($it->job_no); ?></td>
                                        <td><?php echo e($it->full_name ?? 'N/A'); ?></td>
                                        <td><?php echo e($it->dress_name ?? 'N/A'); ?></td>
                                        <td class="text-end"><?php echo e($it->qty); ?></td>

                                        <td>
                                            <span class="badge bg-info" style="width: 75px;">
                                                <?php echo e($it->stage_name ?? 'N/A'); ?>

                                            </span>
                                        </td>

                                        <td>
                                            <?php if(!empty($it->due_date)): ?>
                                                <?php echo e(\Carbon\Carbon::parse($it->due_date)->format('d M Y')); ?>

                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php echo e(\Carbon\Carbon::parse($it->updated_at)->format('d M Y, h:i A')); ?>

                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            No production items found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    
    <div class="row g-3 mt-1">
        <div class="col-12 col-lg-6">
            <div class="card stat-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Quick Summary</h5>
                    <span class="muted">Today</span>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Ready Items</span>
                        <b><?php echo e($readyForDeliveryCount); ?></b>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Ready Qty</span>
                        <b><?php echo e($readyForDeliveryQty); ?></b>
                    </div>

                    <?php if($hasDueDate): ?>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-danger">Overdue Items</span>
                            <b class="text-danger"><?php echo e($overdueCount); ?></b>
                        </div>
                        <div class="muted">Based on job due date</div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            Overdue tracking disabled (jobs.due_date column not found).
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="col-12 col-lg-6">
            <div class="card stat-card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">Use <b>Measurements</b> only when template is selected.</li>
                        <li class="mb-2">Make sure <b>Job Due Date</b> and <b>Batch Due Date</b> are filled.</li>
                        <li class="mb-0">If item is delayed, check <b>Overdue Items</b> count.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Production Dashboard'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/tailoring/dashboard/production.blade.php ENDPATH**/ ?>