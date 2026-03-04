

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Tailoring Jobs', 'subtitle' => 'Jobs List'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <style>
        .muted-help { font-size: 12px; color: #6c757d; }
        .pill { padding: 2px 10px; border-radius: 999px; font-size: 12px; background: rgba(13,110,253,.08); }
        .table td, .table th { vertical-align: middle; }
        .actions { min-width: 190px; }
        @media (max-width: 768px){
            .actions { min-width: 140px; }
        }
        .money { font-weight: 700; }
        .badge-soft { background: rgba(13,110,253,.10); color: #0d6efd; border:1px solid rgba(13,110,253,.15); }
    </style>

    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="card-title mb-0">Jobs</h5>
                    <div class="muted-help">View / Edit tailoring jobs  (Job → Batches → Items).</div>
                </div>

                <div class="d-flex gap-2">
                    <a href="<?php echo e(route('tailoring.jobs.createWizard')); ?>" class="btn btn-primary">
                        + Create Job
                    </a>
                </div>
            </div>
        </div>
<?php if(session('success')): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Success',
    text: '<?php echo e(session('success')); ?>',
    timer: 2000,
    showConfirmButton: false
});
</script>
<?php endif; ?>
        <div class="card-body">
            
           
<div class="d-flex justify-content-end mb-3">
    <form method="GET" action="<?php echo e(route('tailoring.jobs.index')); ?>" class="d-flex gap-2">

        <div class="input-group" style="width:320px;">
            <span class="input-group-text bg-white">
                <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
            </span>

            <input type="text"
                name="q"
                class="form-control"
                value="<?php echo e(request('q')); ?>"
                placeholder="Search Jobs...">
        </div>

        <button class="btn btn-dark">
            Search
        </button>

        <a class="btn btn-outline-secondary"
           href="<?php echo e(route('tailoring.jobs.index')); ?>">
            Reset
        </a>

    </form>
</div>
            <div class="table-responsive">
                <table class="table table-hover table-centered">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 120px;">Job No</th>
                            <th style="min-width: 220px;">Customer</th>
                            <th style="min-width: 120px;">Job Date</th>
                            <th style="min-width: 120px;">Due Date</th>

                            
                            <th style="min-width: 140px;">Status</th>

                            
                            <th style="min-width: 200px;">Progress</th>

                            
                            <th style="min-width: 140px;" class="text-end">Total Amount</th>

                            <th class="actions">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $totalQty = (int)($j->total_qty ?? 0);
                                $completedQty = (int)($j->completed_qty ?? 0);

                                // avoid divide by zero
                                $progressPercent = $totalQty > 0 ? (int)round(($completedQty / $totalQty) * 100) : 0;

                                if ($totalQty === 0) {
                                    $statusText = 'No Items';
                                    $statusBadge = 'bg-secondary';
                                } elseif ($completedQty >= $totalQty) {
                                    $statusText = 'Completed';
                                    $statusBadge = 'bg-success';
                                } elseif ($completedQty > 0) {
                                    $statusText = 'In Progress';
                                    $statusBadge = 'bg-warning';
                                } else {
                                    $statusText = 'Pending';
                                    $statusBadge = 'bg-danger';
                                }

                                $amount = (float)($j->total_amount ?? 0);
                            ?>

                            <tr>
                                <td>
                                    <div><b><?php echo e($j->job_no); ?></b></div>
                                    
                                </td>

                                <td>
                                    <div><b><?php echo e($j->customer?->full_name ?? '-'); ?></b></div>
                                    <div class="muted-help"><?php echo e($j->customer?->phone ?? '-'); ?></div>
                                </td>

                                <td><?php echo e($j->job_date?->format('d M Y') ?? '-'); ?></td>
                                <td><?php echo e($j->due_date?->format('d M Y') ?? '-'); ?></td>

                                
                                <td>
                                    <span class="badge <?php echo e($statusBadge); ?>" style="width: 75px;"><?php echo e($statusText); ?></span>
                                </td>

                               
                                
                                <td>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <span class="pill">Batches: <?php echo e($j->batches_count ?? 0); ?></span>
                                        <span class="pill">Items: <?php echo e($j->items_count ?? 0); ?></span>
                                        <span class="pill">Done: <?php echo e($completedQty); ?>/<?php echo e($totalQty); ?> (<?php echo e($progressPercent); ?>%)</span>
                                    </div>

                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar" role="progressbar"
                                            style="width: <?php echo e($progressPercent); ?>%;"
                                            aria-valuenow="<?php echo e($progressPercent); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </td>

                                
                                <td class="text-end">
                                    <div class="money"><?php echo e(number_format($amount, 2)); ?></div>
                                    <div class="muted-help">LKR</div>
                                </td>

   <td>
    <div class="d-flex gap-2">

        <a class="btn btn-outline-dark btn-sm w-100"
           href="<?php echo e(route('tailoring.jobs.show', $j)); ?>">
            View
        </a>

        <a class="btn btn-primary btn-sm w-100"
           href="<?php echo e(route('tailoring.jobs.editWizard', $j)); ?>">
            Edit
        </a>

        <form action="<?php echo e(route('tailoring.jobs.destroy', $j)); ?>" method="POST" class="delete-form w-100">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>

            <button type="button" class="btn btn-outline-danger btn-sm w-100 btnDelete">
                Delete
            </button>
        </form>

    </div>
</td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No jobs found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    <?php echo e($jobs->links()); ?>

                </div>
            </div>
        </div>
    </div>


    <script>

document.querySelectorAll('.btnDelete').forEach(btn => {

    btn.addEventListener('click', function () {

        let form = this.closest('.delete-form');

        Swal.fire({
            title: 'Delete Job?',
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (result.isConfirmed) {
                form.submit();
            }

        });

    });

});

</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Tailoring Jobs'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/tailoring/jobs/index.blade.php ENDPATH**/ ?>