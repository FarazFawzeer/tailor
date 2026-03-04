

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Hiring Reports', 'subtitle' => 'Sales & Pending'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php
    $statusMap = [
        '' => ['label'=>'All', 'cls'=>'bg-light text-dark border'],
        'issued' => ['label'=>'Issued', 'cls'=>'bg-warning text-dark'],
        'returned' => ['label'=>'Returned', 'cls'=>'bg-success'],
        'cancelled' => ['label'=>'Cancelled', 'cls'=>'bg-secondary'],
    ];
    $badge = $statusMap[$status ?? ''] ?? ['label'=>ucfirst($status), 'cls'=>'bg-secondary'];
?>

<style>
    .report-card { border:1px solid rgba(0,0,0,.08); border-radius:14px; }
    .muted-help { font-size:12px; color:#6c757d; }
    .kpi { border:1px solid rgba(0,0,0,.08); border-radius:14px; padding:12px; background:#fff; }
    .kpi .label { font-size:12px; color:#6c757d; }
    .kpi .val { font-weight:800; font-size:16px; }
    .table thead th { font-size:12px; text-transform:uppercase; letter-spacing:.03em; color:#6c757d; }
</style>

<div class="card report-card">
    <div class="card-header d-flex align-items-start justify-content-between">
        <div>
            <h5 class="card-title mb-0">Hiring Sales Report</h5>
            <div class="muted-help mt-1">
                Date Range: <b><?php echo e($from); ?></b> to <b><?php echo e($to); ?></b>
                <span class="text-muted">|</span>
                Status: <span class="badge <?php echo e($badge['cls']); ?>"><?php echo e($badge['label']); ?></span>
            </div>
        </div>
    </div>

    <div class="card-body">

        
        <div class="border-0  mb-3">
            <div class="card-body py-3">
                <form method="GET">
                    <div class="row g-2 align-items-end justify-content-end">
                        <div class="col-md-2">
                            <label class="form-label mb-1">From</label>
                            <input type="date" name="from" class="form-control" value="<?php echo e($from); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">To</label>
                            <input type="date" name="to" class="form-control" value="<?php echo e($to); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                <?php $__currentLoopData = ['issued','returned','cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($st); ?>" <?php echo e(($status ?? '')===$st?'selected':''); ?>>
                                        <?php echo e(ucfirst($st)); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="ti ti-search me-1"></i> Apply
                            </button>
                            <a href="<?php echo e(route('hiring.reports.sales')); ?>" class="btn btn-light border w-100">
                                <i class="ti ti-refresh me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <div class="kpi">
                    <div class="label">Agreements</div>
                    <div class="val"><?php echo e((int)($summary->agreements ?? 0)); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi">
                    <div class="label">Hire Total</div>
                    <div class="val">Rs <?php echo e(number_format($hireTotal,2)); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi">
                    <div class="label">Fine Total</div>
                    <div class="val">Rs <?php echo e(number_format($fineTotal,2)); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi">
                    <div class="label">Grand Total</div>
                    <div class="val">Rs <?php echo e(number_format($grandTotal,2)); ?></div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-4">
            <div class="col-md-4">
                <div class="kpi">
                    <div class="label">Deposit Collected</div>
                    <div class="val">Rs <?php echo e(number_format($depositTotal,2)); ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kpi">
                    <div class="label">Other Payments (Paid)</div>
                    <div class="val">Rs <?php echo e(number_format($paidTotal,2)); ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kpi">
                    <div class="label">Pending (Outstanding)</div>
                    <div class="val text-danger">Rs <?php echo e(number_format($pending,2)); ?></div>
                    <div class="muted-help">Grand Total - (Deposit + Paid)</div>
                </div>
            </div>
        </div>

        
        <div class="card border mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <b>Daily Summary</b>
                <div class="muted-help">Shows totals per issue date</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Agreements</th>
                                <th class="text-end">Hire</th>
                                <th class="text-end">Fine</th>
                                <th class="text-end">Collected</th>
                                <th class="text-end">Pending</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $daily; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e(\Carbon\Carbon::parse($d['date'])->format('d M Y')); ?></td>
                                    <td class="text-end fw-semibold"><?php echo e($d['agreements']); ?></td>
                                    <td class="text-end">Rs <?php echo e(number_format($d['hire_total'],2)); ?></td>
                                    <td class="text-end">Rs <?php echo e(number_format($d['fine_total'],2)); ?></td>
                                    <td class="text-end fw-semibold">Rs <?php echo e(number_format($d['collected'],2)); ?></td>
                                    <td class="text-end fw-semibold <?php echo e($d['pending']>0?'text-danger':''); ?>">
                                        Rs <?php echo e(number_format($d['pending'],2)); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No data.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        
        <div class="card border">
            <div class="card-header"><b>Agreement Details</b></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Customer</th>
                                <th>Issue</th>
                                <th>Status</th>
                                <th class="text-end">Grand</th>
                                <th class="text-end">Collected</th>
                                <th class="text-end">Pending</th>
                                <th style="width:120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $agreements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $grand = (float)($a->total_hire_amount ?? 0) + (float)($a->fine_amount ?? 0);
                                    $col   = (float)($a->deposit_received ?? 0) + (float)($a->amount_paid ?? 0);
                                    $pend  = max(0, $grand - $col);

                                    $cls = match($a->status){
                                        'issued'=>'bg-warning text-dark',
                                        'returned'=>'bg-success',
                                        'cancelled'=>'bg-secondary',
                                        default=>'bg-secondary'
                                    };
                                ?>
                                <tr>
                                    <td class="fw-bold"><?php echo e($a->agreement_no); ?></td>
                                    <td><?php echo e($a->customer?->full_name ?? 'N/A'); ?></td>
                                    <td><?php echo e(optional($a->issue_date)->format('d M Y')); ?></td>
                                    <td><span class="badge <?php echo e($cls); ?>"><?php echo e(ucfirst($a->status)); ?></span></td>
                                    <td class="text-end fw-semibold">Rs <?php echo e(number_format($grand,2)); ?></td>
                                    <td class="text-end">Rs <?php echo e(number_format($col,2)); ?></td>
                                    <td class="text-end fw-semibold <?php echo e($pend>0?'text-danger':''); ?>">Rs <?php echo e(number_format($pend,2)); ?></td>
                                    <td>
                                        <a class="btn btn-outline-primary btn-sm w-100"
                                           href="<?php echo e(route('hiring.agreements.show', $a->id)); ?>">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="8" class="text-center text-muted py-4">No agreements found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <?php echo e($agreements->links()); ?>

        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Hiring Sales Report'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/hiring/reports/sales.blade.php ENDPATH**/ ?>