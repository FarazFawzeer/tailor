

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Hiring Inventory', 'subtitle' => 'Items'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $statusMap = [
            ''            => ['label' => 'All Status',  'cls' => 'bg-light text-dark border'],
            'available'   => ['label' => 'Available',   'cls' => 'bg-success'],
            'reserved'    => ['label' => 'Reserved',    'cls' => 'bg-warning text-dark'],
            'hired'       => ['label' => 'Hired',       'cls' => 'bg-primary'],
            'maintenance' => ['label' => 'Maintenance', 'cls' => 'bg-danger'],
        ];
        $currentStatus = $status ?? '';
        $statusBadge = $statusMap[$currentStatus] ?? ['label' => ucfirst($currentStatus), 'cls' => 'bg-secondary'];
    ?>

    <style>
        .report-card { border:1px solid rgba(0,0,0,.08); border-radius:14px; }
        .muted-help { font-size: 12px; color: #6c757d; }
        .pill { padding: 2px 10px; border-radius: 999px; font-size: 12px; background: rgba(13,110,253,.08); }
        .thumb { width: 42px; height: 42px; border-radius: 10px; object-fit: cover; border:1px solid rgba(0,0,0,.08); }
        .table thead th { font-size: 12px; text-transform: uppercase; letter-spacing: .03em; color:#6c757d; }
        .filters { background: rgba(13,110,253,.03); border:1px solid rgba(13,110,253,.10); border-radius: 14px; }
        .btn-icon { display:inline-flex; align-items:center; gap:.35rem; }
    </style>

    <div class="card report-card">
        <div class="card-header d-flex align-items-start justify-content-between">
            <div>
                <h5 class="card-title mb-0">Hire Items</h5>
                <div class="muted-help mt-1">
                    Search & filter your hiring inventory. Current filter:
                    <span class="badge <?php echo e($statusBadge['cls']); ?>"><?php echo e($statusBadge['label']); ?></span>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="<?php echo e(route('hiring.items.create')); ?>" class="btn btn-primary btn-icon">
                    <i class="ti ti-plus"></i> Create
                </a>
            </div>
        </div>

        <div class="card-body">
            
      
<div class="mb-3">
    <div class="card-body py-3">

        <form method="GET">
            <div class="row g-2 justify-content-end">

                
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="ti ti-search"></i>
                        </span>
                        <input
                            type="text"
                            name="q"
                            class="form-control"
                            value="<?php echo e($q ?? ''); ?>"
                            placeholder="Search item code, name or category">
                    </div>
                </div>

                
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <?php $__currentLoopData = ['available','reserved','hired','maintenance']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($st); ?>" <?php echo e(($status ?? '') === $st ? 'selected' : ''); ?>>
                                <?php echo e(ucfirst($st)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-search me-1"></i> Search
                    </button>

                    <a href="<?php echo e(route('hiring.items.index')); ?>" class="btn btn-light border w-100">
                        <i class="ti ti-refresh"></i>Reset
                    </a>
                </div>

            </div>
        </form>

    </div>
</div>

            
            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>Code</th>
                        <th>Category</th>
                        <th class="text-center">Sizes</th>
                        <th class="text-center">Total Qty</th>
                        <th class="text-end">Price</th>
                        <th>Status</th>
                        <th style="width: 260px;">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $imgPath = $it->images->first()?->image_path
                                ? asset($it->images->first()->image_path)
                                : asset('/images/users/avatar-6.jpg');

                            $variants = $it->variants ?? collect();     // requires ->with('variants') in controller
                            $sizesCount = $variants->count();
                            $totalQty = (int) $variants->sum('qty');

                            $badgeCls = match($it->status){
                                'available' => 'bg-success',
                                'reserved' => 'bg-warning text-dark',
                                'hired' => 'bg-primary',
                                'maintenance' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                        ?>

                        <tr id="row-<?php echo e($it->id); ?>">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img class="thumb" src="<?php echo e($imgPath); ?>" alt="img">
                                    <div>
                                        <div class="fw-bold"><?php echo e($it->name); ?></div>
                                        <div class="text-muted small">
                                            <?php echo e($it->notes ? \Illuminate\Support\Str::limit($it->notes, 55) : ''); ?>

                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="fw-bold"><?php echo e($it->item_code); ?></td>
                            <td><?php echo e($it->category ?? '-'); ?></td>

                            <td class="text-center">
                                <span class="badge bg-light text-dark border"><?php echo e($sizesCount); ?></span>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-light text-dark border"><?php echo e(number_format($totalQty)); ?></span>
                            </td>

                            <td class="text-end fw-semibold"><?php echo e(number_format((float)$it->hire_price, 2)); ?></td>

                            <td>
                                <span class="badge <?php echo e($badgeCls); ?>" style="width: 75px;"><?php echo e(ucfirst($it->status)); ?></span>
                                <?php if(!$it->is_active): ?>
                                    <div class="text-muted small">Inactive</div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex gap-2">
                                    
                                    <a href="<?php echo e(route('hiring.items.show', $it->id)); ?>"
                                       class="btn btn-outline-primary btn-sm w-100 btn-icon">
                                        <i class="ti ti-eye"></i> View
                                    </a>

                                    
                                    <a href="<?php echo e(route('hiring.items.edit', $it->id)); ?>"
                                       class="btn btn-outline-dark btn-sm w-100 btn-icon">
                                        <i class="ti ti-edit"></i> Edit
                                    </a>

                                    
                                    <button type="button"
                                            class="btn btn-danger btn-sm w-100 btn-delete btn-icon"
                                            data-id="<?php echo e($it->id); ?>">
                                        <i class="ti ti-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No items found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <?php echo e($items->links()); ?>

            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;

                Swal.fire({
                    title: 'Delete this item?',
                    text: "You can't undo this.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete'
                }).then((r) => {
                    if (!r.isConfirmed) return;

                    fetch("<?php echo e(url('hiring/items')); ?>/" + id, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>",
                            "Accept": "application/json"
                        }
                    }).then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            Swal.fire('Error', data.message || 'Something went wrong', 'error');
                            return;
                        }
                        document.getElementById('row-' + id)?.remove();
                        Swal.fire('Deleted', data.message || 'Item deleted', 'success');
                    }).catch(() => {
                        Swal.fire('Error', 'Network error. Please try again.', 'error');
                    });
                });
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Hire Items'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/hiring/items/index.blade.php ENDPATH**/ ?>