

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Hiring', 'subtitle' => 'Agreements'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $statusMap = [
            ''          => ['label' => 'All Status', 'cls' => 'bg-light text-dark border'],
            'issued'    => ['label' => 'Issued',    'cls' => 'bg-warning text-dark'],
            'returned'  => ['label' => 'Returned',  'cls' => 'bg-success'],
            'cancelled' => ['label' => 'Cancelled', 'cls' => 'bg-secondary'],
        ];
        $currentStatus = $status ?? '';
        $statusBadge = $statusMap[$currentStatus] ?? ['label' => ucfirst($currentStatus), 'cls' => 'bg-secondary'];
    ?>

    <style>
        .report-card { border:1px solid rgba(0,0,0,.08); border-radius:14px; }
        .muted-help { font-size: 12px; color: #6c757d; }
        .pill { padding: 2px 10px; border-radius: 999px; font-size: 12px; background: rgba(13,110,253,.08); }
        .table thead th { font-size: 12px; text-transform: uppercase; letter-spacing: .03em; color:#6c757d; }
        .btn-icon { display:inline-flex; align-items:center; gap:.35rem; }
    </style>

    <div class="card report-card">
        <div class="card-header d-flex align-items-start justify-content-between">
            <div>
                <h5 class="card-title mb-0">Hire Agreements</h5>
                <div class="muted-help mt-1">
                    Issue / Return agreements and track fine. Current filter:
                    <span class="badge <?php echo e($statusBadge['cls']); ?>"><?php echo e($statusBadge['label']); ?></span>
                </div>
            </div>

            <a href="<?php echo e(route('hiring.agreements.create')); ?>" class="btn btn-primary btn-icon">
                <i class="ti ti-plus"></i> Create
            </a>
        </div>

        <div class="card-body">

            
            <div class=" border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <form method="GET">
                        <div class="row g-2 justify-content-end">

                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="ti ti-search"></i></span>
                                    <input name="q" class="form-control"
                                           value="<?php echo e($q ?? ''); ?>"
                                           placeholder="Search agreement no / customer name">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <?php $__currentLoopData = ['issued','returned','cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($st); ?>" <?php echo e(($status ?? '') === $st ? 'selected' : ''); ?>>
                                            <?php echo e(ucfirst($st)); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="col-md-2 d-flex gap-2">
                                <button class="btn btn-primary w-100 btn-icon" type="submit">
                                    <i class="ti ti-search"></i> Search
                                </button>
                                <a href="<?php echo e(route('hiring.agreements.index')); ?>" class="btn btn-light border w-100" title="Reset">
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
                        <th>Agreement</th>
                        <th>Customer</th>
                        <th>Issue</th>
                        <th>Expected Return</th>
                        <th>Status</th>
                        <th class="text-end">Hire Total</th>
                        <th class="text-end">Fine</th>
                        <th style="width: 280px;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $agreements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $badgeCls = match($a->status){
                                'issued' => 'bg-warning text-dark',
                                'returned' => 'bg-success',
                                'cancelled' => 'bg-secondary',
                                default => 'bg-secondary'
                            };
                        ?>
                        <tr id="row-<?php echo e($a->id); ?>">
                            <td class="fw-bold"><?php echo e($a->agreement_no); ?></td>
                            <td><?php echo e($a->customer?->full_name ?? 'N/A'); ?></td>
                            <td><?php echo e(optional($a->issue_date)->format('d M Y')); ?></td>
                            <td><?php echo e(optional($a->expected_return_date)->format('d M Y')); ?></td>
                            <td><span class="badge <?php echo e($badgeCls); ?>" style="width: 75px;"><?php echo e(ucfirst($a->status)); ?></span></td>
                            <td class="text-end fw-semibold"><?php echo e(number_format((float)$a->total_hire_amount, 2)); ?></td>
                            <td class="text-end fw-semibold"><?php echo e(number_format((float)$a->fine_amount, 2)); ?></td>

                            <td>
                                <div class="d-flex gap-2">
                                    
                                    <a href="<?php echo e(route('hiring.agreements.show', $a->id)); ?>"
                                       class="btn btn-outline-primary btn-sm w-100 btn-icon justify-content-center align-items-cente">
                                        <i class="ti ti-eye"></i> View
                                    </a>

                                    
                                    <?php if($a->status !== 'returned'): ?>
                                        <a href="<?php echo e(route('hiring.agreements.edit', $a->id)); ?>"
                                           class="btn btn-outline-dark btn-sm w-100 btn-icon justify-content-center align-items-cente">
                                            <i class="ti ti-edit"></i> Edit
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-outline-dark btn-sm w-100" disabled title="Returned agreements cannot be edited">
                                            <i class="ti ti-edit"></i> Edit
                                        </button>
                                    <?php endif; ?>

                                    
                                    <?php if($a->status !== 'returned'): ?>
                                        <button type="button"
                                                class="btn btn-danger btn-sm w-100 btn-icon btn-delete justify-content-center align-items-cente"
                                                data-id="<?php echo e($a->id); ?>">
                                            <i class="ti ti-trash"></i> Delete
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-danger btn-sm w-100" disabled title="Returned agreements cannot be deleted">
                                            <i class="ti ti-trash"></i> Delete
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No agreements found.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <?php echo e($agreements->links()); ?>

            </div>
        </div>
    </div>

    <script>
        // Delete Agreement (AJAX)
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;

                Swal.fire({
                    title: 'Delete this agreement?',
                    text: "You can't undo this.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete'
                }).then((r) => {
                    if (!r.isConfirmed) return;

                    fetch("<?php echo e(url('/hiring/agreements/delete')); ?>/" + id, {
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
                        Swal.fire('Deleted', data.message || 'Agreement deleted', 'success');
                    }).catch(() => {
                        Swal.fire('Error', 'Network error. Please try again.', 'error');
                    });
                });
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Hire Agreements'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/hiring/agreements/index.blade.php ENDPATH**/ ?>