<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice <?php echo e($invoiceNo); ?></title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .wrap { padding: 20px 24px; }
        .row { width: 100%; }
        .muted { color: #666; }
        .title { font-size: 20px; font-weight: 700; margin: 0; }
        .small { font-size: 11px; }
        .hr { height: 1px; background: #eee; margin: 14px 0; }

        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e6e6e6; padding: 8px; vertical-align: top; }
        th { background: #f5f5f5; font-weight: 700; text-align: left; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .no-border td, .no-border th { border: none; }

        .totals td { border: none; padding: 4px 0; }
        .totals .label { text-align: right; color: #555; }
        .totals .val { text-align: right; font-weight: 700; }

        .badge { display:inline-block; padding: 3px 8px; border-radius: 999px; background:#f2f2f2; font-size: 11px; }
        .footer { margin-top: 26px; }
        .signature { margin-top: 28px; }
        .signature .line { width: 220px; height: 1px; background: #333; margin-top: 22px; }
    </style>
</head>
<body>
<div class="wrap">

    
    <table class="no-border">
        <tr>
            <td style="width: 60%;">
                <p class="title"><?php echo e($company['name']); ?></p>
                <div class="muted small">
                    <?php echo e($company['address']); ?><br>
                    Phone: <?php echo e($company['phone']); ?><br>
                    Email: <?php echo e($company['email']); ?>

                </div>
            </td>
            <td class="text-right" style="width: 40%;">
                <p class="title" style="margin-bottom:6px;">INVOICE</p>
                <div class="small">
                    <b>Invoice No:</b> <?php echo e($invoiceNo); ?><br>
                    <b>Invoice Date:</b> <?php echo e(\Carbon\Carbon::parse($invoiceDate)->format('d M Y')); ?><br>
                    <b>Job No:</b> <?php echo e($job->job_no); ?><br>
                    <span class="badge">Stage: <?php echo e($job->currentStage?->name ?? '-'); ?></span>
                </div>
            </td>
        </tr>
    </table>

    <div class="hr"></div>

    
    <table class="no-border">
        <tr>
            <td style="width: 55%;">
                <b>Bill To</b><br>
                <div style="margin-top:6px;">
                    <b><?php echo e($job->customer?->full_name ?? '-'); ?></b><br>
                    <span class="muted">Phone:</span> <?php echo e($job->customer?->phone ?? '-'); ?><br>
                    <?php if(!empty($job->customer?->email)): ?>
                        <span class="muted">Email:</span> <?php echo e($job->customer?->email); ?><br>
                    <?php endif; ?>
                    <?php if(!empty($job->customer?->address)): ?>
                        <span class="muted">Address:</span> <?php echo e($job->customer?->address); ?><br>
                    <?php endif; ?>
                </div>
            </td>

            <td style="width: 45%;">
                <b>Job Details</b><br>
                <div style="margin-top:6px;">
                    <span class="muted">Job Date:</span> <?php echo e($job->job_date?->format('d M Y') ?? '-'); ?><br>
                    <span class="muted">Due Date:</span> <?php echo e($job->due_date?->format('d M Y') ?? '-'); ?><br>
                    <span class="muted">Notes:</span> <?php echo e($job->notes ?? '-'); ?>

                </div>
            </td>
        </tr>
    </table>

    <div class="hr"></div>

    
    <table>
        <thead>
            <tr>
                <th style="width: 70px;">Batch</th>
                <th>Dress</th>
                <th style="width: 140px;">Template</th>
                <th class="text-center" style="width: 60px;">Qty</th>
                <th class="text-right" style="width: 90px;">Unit Price</th>
                <th class="text-right" style="width: 100px;">Line Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ln): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($ln['batch_no']); ?></td>
                    <td>
                        <b><?php echo e($ln['dress']); ?></b>
                        <?php if(!empty($ln['notes'])): ?>
                            <div class="muted small">Note: <?php echo e($ln['notes']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($ln['template']); ?></td>
                    <td class="text-center"><?php echo e($ln['qty']); ?></td>
                    <td class="text-right"><?php echo e(number_format((float)$ln['unit_price'], 2)); ?></td>
                    <td class="text-right"><b><?php echo e(number_format((float)$ln['line_total'], 2)); ?></b></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="text-center muted">No items.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    
    <table class="no-border" style="margin-top: 14px;">
        <tr>
            <td style="width: 55%;">
                <div class="small muted">
                    <b>Terms:</b> Please check measurements before final stitching.<br>
                    <b>Payment:</b> Pay on delivery / as agreed.
                </div>
            </td>
            <td style="width: 45%;">
                <table class="totals" style="width:100%;">
                    <tr>
                        <td class="label">Sub Total</td>
                        <td class="val"><?php echo e(number_format((float)$subTotal, 2)); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Discount</td>
                        <td class="val"><?php echo e(number_format((float)$discount, 2)); ?></td>
                    </tr>
                    <tr>
                        <td class="label" style="font-size: 13px;"><b>Grand Total</b></td>
                        <td class="val" style="font-size: 13px;"><b><?php echo e(number_format((float)$grandTotal, 2)); ?></b></td>
                    </tr>
                    <tr>
                        <td class="label muted">Currency</td>
                        <td class="val muted">LKR</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer">
        <div class="signature">
            <div class="line"></div>
            <div class="small muted">Authorized Signature</div>
        </div>

        <div class="small muted" style="margin-top: 12px;">
            This is a system-generated invoice.
        </div>
    </div>

</div>
</body>
</html><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/tailoring/jobs/invoice_pdf.blade.php ENDPATH**/ ?>