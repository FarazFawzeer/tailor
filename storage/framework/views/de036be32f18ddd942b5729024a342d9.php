

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Hiring Inventory', 'subtitle' => 'View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php
    // Safe totals
    $variants = $item->variants ?? collect();
    $images   = $item->images ?? collect();
    $totalQty = (int) $variants->sum('qty');

    $statusMap = [
        'available'   => ['label' => 'Available',   'cls' => 'bg-success'],
        'reserved'    => ['label' => 'Reserved',    'cls' => 'bg-warning text-dark'],
        'hired'       => ['label' => 'Hired',       'cls' => 'bg-primary'],
        'maintenance' => ['label' => 'Maintenance', 'cls' => 'bg-danger'],
    ];

    $status = $statusMap[$item->status] ?? ['label' => ucfirst($item->status ?? 'N/A'), 'cls' => 'bg-secondary'];
?>

<style>
    .report-card { border:1px solid rgba(0,0,0,.08); border-radius:14px; }
    .muted-help { font-size: 12px; color: #6c757d; }
    .pill { padding: 4px 10px; border-radius: 999px; font-size: 12px; background: rgba(13,110,253,.08); }
    .stat { border:1px solid rgba(0,0,0,.08); border-radius:12px; padding:12px; }
    .kv { border:1px solid rgba(0,0,0,.06); border-radius:12px; padding:14px; }
    .kv .k { font-size: 12px; color:#6c757d; }
    .kv .v { font-weight: 600; }
    .thumb { width:100%; height:140px; object-fit:cover; border-radius:12px; border:1px solid rgba(0,0,0,.08); }
    .table thead th { font-size: 12px; text-transform: uppercase; letter-spacing: .03em; color:#6c757d; }
</style>

<div class="row g-3">

    
    <div class="col-lg-8">
        <div class="card report-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0"><?php echo e($item->name); ?></h5>
                    <div class="muted-help mt-1">
                        Item Code: <span class="pill"><?php echo e($item->item_code); ?></span>
                        <span class="ms-2 badge <?php echo e($status['cls']); ?>"><?php echo e($status['label']); ?></span>
                        <?php if($item->is_active): ?>
                            <span class="ms-2 badge bg-success-subtle text-success border border-success-subtle">Active</span>
                        <?php else: ?>
                            <span class="ms-2 badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="<?php echo e(route('hiring.items.edit', $item->id)); ?>" class="btn btn-outline-primary">
                        <i class="ti ti-edit me-1"></i>Edit
                    </a>
                    <a href="<?php echo e(route('hiring.items.index')); ?>" class="btn btn-secondary">
                        Back
                    </a>
                </div>
            </div>

            <div class="card-body">

                
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <div class="stat">
                            <div class="muted-help">Total Qty</div>
                            <div class="fs-5 fw-bold"><?php echo e(number_format($totalQty)); ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat">
                            <div class="muted-help">Sizes</div>
                            <div class="fs-5 fw-bold"><?php echo e(number_format($variants->count())); ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat">
                            <div class="muted-help">Hire Price</div>
                            <div class="fs-5 fw-bold"><?php echo e(number_format((float)$item->hire_price, 2)); ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat">
                            <div class="muted-help">Deposit</div>
                            <div class="fs-5 fw-bold"><?php echo e(number_format((float)($item->deposit_amount ?? 0), 2)); ?></div>
                        </div>
                    </div>
                </div>

                
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="kv">
                            <div class="k">Category</div>
                            <div class="v"><?php echo e($item->category ?: '—'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="kv">
                            <div class="k">Default Color</div>
                            <div class="v"><?php echo e($item->color ?: '—'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="kv">
                            <div class="k">Created</div>
                            <div class="v"><?php echo e(optional($item->created_at)->format('Y-m-d h:i A') ?: '—'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="kv">
                            <div class="k">Updated</div>
                            <div class="v"><?php echo e(optional($item->updated_at)->format('Y-m-d h:i A') ?: '—'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="kv">
                            <div class="k">Notes</div>
                            <div class="v fw-normal"><?php echo e($item->notes ?: '—'); ?></div>
                        </div>
                    </div>
                </div>

                
                <div class="mt-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="mb-0">Sizes & Quantities</h6>
                        <span class="muted-help">Total: <?php echo e(number_format($totalQty)); ?></span>
                    </div>
                    <hr class="mt-2 mb-3">

                    <?php if($variants->count()): ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 30%">Size</th>
                                        <th style="width: 20%">Color</th>
                                        <th style="width: 20%">Qty</th>
                                        <th style="width: 30%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="fw-semibold"><?php echo e($v->size); ?></td>
                                            <td><?php echo e($v->color ?: '—'); ?></td>
                                            <td class="fw-bold"><?php echo e(number_format((int)$v->qty)); ?></td>
                                            <td>
                                                <?php if($v->is_active ?? true): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">No size/quantity rows found for this item.</div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    
    <div class="col-lg-4">
        <div class="card report-card">
            <div class="card-header">
                <h6 class="mb-0">Images</h6>
                <div class="muted-help">Click to open full image.</div>
            </div>
            <div class="card-body">
                <?php if($images->count()): ?>
                    <div class="row g-2">
                        <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-6">
                                <a href="<?php echo e(asset($img->image_path)); ?>" target="_blank" class="text-decoration-none">
                                    <img src="<?php echo e(asset($img->image_path)); ?>" class="thumb" alt="Image">
                                </a>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border mb-0">
                        No images uploaded for this item.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="card report-card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="<?php echo e(route('hiring.items.edit', $item->id)); ?>" class="btn btn-outline-primary w-100">
                    <i class="ti ti-edit me-1"></i>Edit Item
                </a>

                
                
                
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Hire Item View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/hiring/items/show.blade.php ENDPATH**/ ?>