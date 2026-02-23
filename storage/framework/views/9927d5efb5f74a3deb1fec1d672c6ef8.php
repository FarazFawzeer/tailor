

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Hiring', 'subtitle' => 'Availability Dashboard'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Items</p>
                    <h3 class="mb-0"><?php echo e($totalItems); ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Available Now</p>
                    <h3 class="mb-0 text-success"><?php echo e($availableCount); ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Hired Out</p>
                    <h3 class="mb-0 text-primary"><?php echo e($hiredCount); ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Overdue Agreements</p>
                    <h3 class="mb-0 text-danger"><?php echo e($overdueCount); ?></h3>
                    <div class="mt-2">
                        <a class="btn btn-outline-danger btn-sm" href="<?php echo e(route('hiring.availability.overdue')); ?>">View All</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Maintenance</p>
                    <h4 class="mb-0"><?php echo e($maintenanceCount); ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Damaged</p>
                    <h4 class="mb-0"><?php echo e($damagedCount); ?></h4>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Availability by Category</h5>
            <p class="card-subtitle mb-0">Shows stock split (Available vs Hired) and utilization.</p>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th>Total</th>
                            <th>Available</th>
                            <th>Hired</th>
                            <th>Utilization %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $categoryStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($row->category); ?></td>
                                <td><b><?php echo e($row->total); ?></b></td>
                                <td><span class="badge bg-success"><?php echo e($row->available); ?></span></td>
                                <td><span class="badge bg-primary"><?php echo e($row->hired); ?></span></td>
                                <td><?php echo e(number_format((float)$row->utilization, 1)); ?>%</td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No data.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Upcoming Returns (Next 7 days)</h5>
                <p class="card-subtitle mb-0">Agreements that should return soon.</p>
            </div>
            <a class="btn btn-outline-dark btn-sm" href="<?php echo e(route('hiring.availability.upcoming')); ?>">View All</a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Agreement</th>
                            <th>Customer</th>
                            <th>Expected Return</th>
                            <th>Items Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $upcomingReturns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><b><?php echo e($r->agreement_no); ?></b></td>
                                <td><?php echo e($r->full_name); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($r->expected_return_date)->format('d M Y')); ?></td>
                                <td><span class="badge bg-primary"><?php echo e($r->items_out); ?></span></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No upcoming returns.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Overdue (Top 10)</h5>
            <p class="card-subtitle mb-0">These agreements are past expected return date.</p>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Agreement</th>
                            <th>Issue</th>
                            <th>Expected Return</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $overdueAgreements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><b><?php echo e($o->agreement_no); ?></b></td>
                                <td><?php echo e(\Carbon\Carbon::parse($o->issue_date)->format('d M Y')); ?></td>
                                <td><span class="badge bg-danger">
                                    <?php echo e(\Carbon\Carbon::parse($o->expected_return_date)->format('d M Y')); ?>

                                </span></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">No overdue agreements.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Availability Dashboard'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/hiring/availability/index.blade.php ENDPATH**/ ?>