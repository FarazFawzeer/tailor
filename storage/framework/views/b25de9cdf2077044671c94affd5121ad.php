

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Tailoring', 'subtitle' => 'Handover'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <style>
        .muted { font-size: 12px; color: #6c757d; }
        .stage-pill { border: 1px solid rgba(0,0,0,.08); border-radius: 12px; padding: 10px 12px; background: #fff; }
        .stage-pill .name { font-size: 13px; color: #6c757d; }
        .stage-pill .num { font-size: 20px; font-weight: 700; line-height: 1; }
        .stage-pill .qty { font-size: 12px; color: #6c757d; }

        .job-card { border: 1px solid rgba(0,0,0,.08); border-radius: 12px; overflow: hidden; }
        .job-head { background: rgba(0,0,0,.03); }
        .badge-soft { background: rgba(13,110,253,.08); color: #0d6efd; }
        .table td, .table th { vertical-align: middle; }
        .action-wrap { min-width: 220px; }
    </style>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h5 class="card-title mb-1">Handover Items</h5>
                    <div class="muted">
                        Easy view: <b>Job → Batches/Items</b>. You can do <b>Single</b> or <b>Group Handover</b>.
                        <span class="text-muted">Partial handover supported.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">

            
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input name="q" class="form-control" value="<?php echo e($q ?? ''); ?>" placeholder="Search Job No / Batch No">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <a href="<?php echo e(route('tailoring.handover.index')); ?>" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>

            
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-2 mb-3">
                <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $sum = $stageSummary[$s->id] ?? ['item_count' => 0, 'qty_sum' => 0];
                    ?>
                    <div class="col">
                        <div class="stage-pill h-100">
                            <div class="name"><?php echo e($s->name); ?></div>
                            <div class="d-flex align-items-end gap-2 mt-1">
                                <div class="num"><?php echo e($sum['item_count']); ?></div>
                                <div class="muted mb-1">items</div>
                            </div>
                            <div class="qty">Qty: <b><?php echo e($sum['qty_sum']); ?></b></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <?php if($groupedJobs->count()): ?>
                <div class="accordion" id="handoverJobsAccordion">
                    <?php $accIndex = 0; ?>

                    <?php $__currentLoopData = $groupedJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jobNo => $jobItems): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $accIndex++;
                            $first = $jobItems->first();
                            $job = $first->jobBatch?->job;
                            $customer = $job?->customer?->full_name ?? 'N/A';

                            $totalItems = $jobItems->count();
                            $totalQty = (int)$jobItems->sum('qty');
                        ?>

                        <div class="accordion-item job-card mb-2">
                            <h2 class="accordion-header" id="heading<?php echo e($accIndex); ?>">
                                <button class="accordion-button <?php echo e($accIndex === 1 ? '' : 'collapsed'); ?>" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapse<?php echo e($accIndex); ?>"
                                    aria-expanded="<?php echo e($accIndex === 1 ? 'true' : 'false'); ?>"
                                    aria-controls="collapse<?php echo e($accIndex); ?>">
                                    <div class="w-100 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <b>Job: <?php echo e($jobNo); ?></b>
                                            <span class="text-muted ms-2">Customer: <?php echo e($customer); ?></span>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center">
                                            <span class="badge badge-soft">Items: <?php echo e($totalItems); ?></span>
                                            <span class="badge bg-secondary">Qty: <?php echo e($totalQty); ?></span>
                                        </div>
                                    </div>
                                </button>
                            </h2>

                            <div id="collapse<?php echo e($accIndex); ?>" class="accordion-collapse collapse <?php echo e($accIndex === 1 ? 'show' : ''); ?>"
                                aria-labelledby="heading<?php echo e($accIndex); ?>" data-bs-parent="#handoverJobsAccordion">
                                <div class="accordion-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:120px;">Batch</th>
                                                    <th>Dress</th>
                                                    <th class="text-end" style="width:80px;">Qty</th>
                                                    <th style="width:140px;">Stage</th>
                                                    <th style="width:110px;">Completed</th>
                                                    <th style="width:100px;">Group</th>
                                                    <th class="action-wrap">Action</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php $__currentLoopData = $jobItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $groupId = $it->parent_item_id ? $it->parent_item_id : $it->id;
                                                        $groupKey = $it->parent_item_id ?: $it->id;
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-light text-dark">
                                                                <?php echo e($it->jobBatch?->batch_no ?? '-'); ?>

                                                            </span>
                                                        </td>

                                                        <td>
                                                            <div class="fw-semibold"><?php echo e($it->dressType?->name ?? 'N/A'); ?></div>
                                                            <div class="muted">
                                                                Updated: <?php echo e(optional($it->updated_at)->format('d M Y, h:i A')); ?>

                                                                <?php if($it->parent_item_id): ?>
                                                                    <span class="badge bg-warning ms-1">Partial</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>

                                                        <td class="text-end fw-bold"><?php echo e((int)$it->qty); ?></td>

                                                        <td>
                                                            <span class="badge bg-info"><?php echo e($it->stage?->name ?? 'N/A'); ?></span>
                                                        </td>

                                                        <td>
                                                            <?php if($it->completed_at): ?>
                                                                <span class="badge bg-success">Yes</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">No</span>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td class="text-muted">#<?php echo e($groupId); ?></td>

                                                        <td>
                                                            <div class="d-flex flex-wrap gap-2">
                                                                <a href="<?php echo e(route('tailoring.handover.group.create', $groupKey)); ?>"
                                                                    class="btn btn-primary btn-sm">
                                                                    Group Handover
                                                                </a>

                                                                <a href="<?php echo e(route('tailoring.handover.history', $it)); ?>"
                                                                    class="btn btn-outline-dark btn-sm">
                                                                    History
                                                                </a>

                                                                <?php if(!$it->completed_at): ?>
                                                                    <a href="<?php echo e(route('tailoring.handover.create', $it)); ?>"
                                                                        class="btn btn-outline-primary btn-sm">
                                                                        Single
                                                                    </a>
                                                                <?php else: ?>
                                                                    <button class="btn btn-secondary btn-sm" disabled>Single</button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <?php echo e($items->links()); ?>

                </div>
            <?php else: ?>
                <div class="text-center text-muted py-4">
                    No items found.
                </div>
            <?php endif; ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Handover'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/tailoring/handover/index.blade.php ENDPATH**/ ?>