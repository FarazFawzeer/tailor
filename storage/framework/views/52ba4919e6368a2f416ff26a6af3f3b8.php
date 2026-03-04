


<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Hiring', 'subtitle' => 'Agreement Details'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $status = $agreement->status;

        $badgeCls = match ($status) {
            'issued' => 'bg-warning text-dark',
            'returned' => 'bg-success',
            'cancelled' => 'bg-secondary',
            default => 'bg-secondary',
        };

        $issue = optional($agreement->issue_date)->format('d M Y');
        $exp   = optional($agreement->expected_return_date)->format('d M Y');
        $act   = $agreement->actual_return_date
            ? \Carbon\Carbon::parse($agreement->actual_return_date)->format('d M Y')
            : null;

        $lines = $agreement->items ?? collect();

        $totalQty   = (int) $lines->sum(fn($l) => (int)($l->qty ?? 0));
        $hireTotal  = (float) $lines->sum(fn($l) => (float)($l->line_total ?? ((float)($l->hire_price ?? 0) * (int)($l->qty ?? 0))));

        // NOTE: Deposit in your system is agreement-level deposit_received (money), NOT per line.
        $depositReceived = (float)($agreement->deposit_received ?? 0);

        // Paid so far (you already used this in return blade)
        $paidSoFar = (float)($agreement->amount_paid ?? 0);

        $fine   = (float)($agreement->fine_amount ?? 0);

        $grand  = $hireTotal + $fine;

        // Balance AFTER deposit + paid
        $balance = max(0, $grand - $depositReceived - $paidSoFar);
    ?>

    <style>
        .report-card { border:1px solid rgba(0,0,0,.08); border-radius:14px; }
        .muted-help { font-size: 12px; color: #6c757d; }
        .thumb { width: 44px; height: 44px; border-radius: 12px; object-fit: cover; border: 1px solid rgba(0,0,0,.08); }
        .kpi { border:1px solid rgba(0,0,0,.08); border-radius:14px; padding:12px; background:#fff; height:100%; }
        .kpi .label { font-size:12px; color:#6c757d; }
        .kpi .val { font-weight:700; font-size:16px; }
        .table thead th { font-size: 12px; text-transform: uppercase; letter-spacing: .03em; color: #6c757d; }
        .btn-icon { display:inline-flex; align-items:center; gap:.35rem; }
        .money-neg { color:#dc3545; font-weight:800; }
        .money-ok  { color:#198754; font-weight:800; }
    </style>

    <div class="card report-card">
        <div class="card-header d-flex align-items-start justify-content-between">
            <div>
                <h5 class="card-title mb-0"><?php echo e($agreement->agreement_no); ?></h5>
                <div class="muted-help mt-1">
                    Customer: <b><?php echo e($agreement->customer?->full_name ?? 'N/A'); ?></b>
                    <?php if($agreement->customer?->phone): ?>
                        <span class="text-muted">|</span> <?php echo e($agreement->customer->phone); ?>

                    <?php endif; ?>
                    <span class="text-muted">|</span>
                    Status: <span class="badge <?php echo e($badgeCls); ?>"><?php echo e(ucfirst($status)); ?></span>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <a href="<?php echo e(route('hiring.agreements.index')); ?>"
                   class="btn btn-light border btn-icon d-flex justify-content-center align-items-center"
                   style="width:150px;">
                    <i class="ti ti-arrow-left me-1"></i> Back
                </a>

                <?php if($agreement->status === 'issued'): ?>
                    <a href="<?php echo e(route('hiring.agreements.edit', $agreement->id)); ?>"
                       class="btn btn-outline-dark btn-icon d-flex justify-content-center align-items-center"
                       style="width:150px;">
                        <i class="ti ti-edit me-1"></i> Edit
                    </a>

                    <a href="<?php echo e(route('hiring.agreements.return', $agreement)); ?>"
                       class="btn btn-success btn-icon d-flex justify-content-center align-items-center"
                       style="width:170px;">
                        <i class="ti ti-check"></i> Return Items
                    </a>
                <?php endif; ?>

                <a href="<?php echo e(route('hiring.agreements.invoice', $agreement->id)); ?>"
                   class="btn btn-outline-primary btn-icon d-flex justify-content-center align-items-center"
                   style="width:150px;" target="_blank">
                    <i class="ti ti-file-invoice"></i> Invoice PDF
                </a>
            </div>
        </div>

        <div class="card-body">

            
            <div class="row g-2 mb-3">
                <div class="col-md-3">
                    <div class="kpi">
                        <div class="label">Issue Date</div>
                        <div class="val"><?php echo e($issue ?? '-'); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="kpi">
                        <div class="label">Expected Return</div>
                        <div class="val"><?php echo e($exp ?? '-'); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="kpi">
                        <div class="label">Actual Return</div>
                        <div class="val"><?php echo e($act ?? '-'); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="kpi">
                        <div class="label">Fine Amount</div>
                        <div class="val">Rs <?php echo e(number_format($fine, 2)); ?></div>
                        <div class="muted-help">Calculated at return</div>
                    </div>
                </div>
            </div>

            
            <div class="row g-2 mb-4">
                <div class="col-md-3">
                    <div class="kpi">
                        <div class="label">Lines</div>
                        <div class="val"><?php echo e($lines->count()); ?></div>
                        <div class="muted-help">Each size is a line</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="kpi">
                        <div class="label">Total Qty</div>
                        <div class="val"><?php echo e($totalQty); ?></div>
                        <div class="muted-help">Sum of all sizes</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="kpi">
                        <div class="label">Hire Total</div>
                        <div class="val">Rs <?php echo e(number_format($hireTotal, 2)); ?></div>
                        <div class="muted-help">Σ (Price × Qty)</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="kpi">
                        <div class="label">Balance</div>
                        <div class="val <?php echo e($balance > 0 ? 'money-neg' : 'money-ok'); ?>">
                            Rs <?php echo e(number_format($balance, 2)); ?>

                        </div>
                        <div class="muted-help">
                            Grand (Hire+Fine) - Deposit - Paid
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card border mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <b>Payment Summary</b>
                    <span class="badge bg-light text-dark border">
                        Grand: Rs <?php echo e(number_format($grand, 2)); ?>

                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <div class="kpi">
                                <div class="label">Hire Total</div>
                                <div class="val">Rs <?php echo e(number_format($hireTotal, 2)); ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi">
                                <div class="label">Fine</div>
                                <div class="val">Rs <?php echo e(number_format($fine, 2)); ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi">
                                <div class="label">Deposit Received</div>
                                <div class="val">Rs <?php echo e(number_format($depositReceived, 2)); ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi">
                                <div class="label">Paid So Far</div>
                                <div class="val">Rs <?php echo e(number_format($paidSoFar, 2)); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="alert <?php echo e($balance > 0 ? 'alert-warning' : 'alert-success'); ?> mt-3 mb-0">
                        <?php if($balance > 0): ?>
                            Pending amount: <b>Rs <?php echo e(number_format($balance, 2)); ?></b>
                        <?php else: ?>
                            No pending amount. Payment completed.
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <?php if($agreement->notes): ?>
                <div class="alert alert-light border">
                    <b>Notes:</b> <?php echo e($agreement->notes); ?>

                </div>
            <?php endif; ?>

            
            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Code</th>
                            <th>Size</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $agreement->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ai): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $thumb = $ai->item?->images?->first()?->image_path
                                    ? asset($ai->item->images->first()->image_path)
                                    : asset('/images/users/avatar-6.jpg');

                                $price = (float) ($ai->hire_price ?? 0);
                                $qty   = (int) ($ai->qty ?? 0);
                                $lt    = (float) ($ai->line_total ?? ($price * $qty));
                            ?>

                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img class="thumb" src="<?php echo e($thumb); ?>" alt="img">
                                        <div>
                                            <div class="fw-bold"><?php echo e($ai->item?->name ?? 'N/A'); ?></div>
                                            <div class="text-muted small"><?php echo e($ai->item?->category ?? ''); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-bold"><?php echo e($ai->item?->item_code ?? '-'); ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo e($ai->size ?? '-'); ?></span></td>
                                <td class="text-end">Rs <?php echo e(number_format($price, 2)); ?></td>
                                <td class="text-end fw-semibold"><?php echo e($qty); ?></td>
                                <td class="text-end fw-semibold">Rs <?php echo e(number_format($lt, 2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No items found in this agreement.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                    <?php if(($agreement->items ?? collect())->count() > 0): ?>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="4" class="text-end">Totals</th>
                                <th class="text-end"><?php echo e($totalQty); ?></th>
                                <th class="text-end">Rs <?php echo e(number_format($hireTotal, 2)); ?></th>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Agreement View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/hiring/agreements/show.blade.php ENDPATH**/ ?>