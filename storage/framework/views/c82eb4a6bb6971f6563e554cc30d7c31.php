

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Tailoring Jobs', 'subtitle' => 'View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title mb-0">Jobs</h5>
                <p class="card-subtitle">All tailoring jobs (Job → Batches → Items).</p>
            </div>
            <a href="<?php echo e(route('tailoring.jobs.create')); ?>" class="btn btn-primary">+ Create Job</a>
        </div>

        <div class="card-body">

            <form method="GET" action="<?php echo e(route('tailoring.jobs.index')); ?>" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" value="<?php echo e(request('q')); ?>"
                        placeholder="Search by Job No / Customer name / phone">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-outline-secondary w-100" href="<?php echo e(route('tailoring.jobs.index')); ?>">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered">
                    <thead class="table-light">
                        <tr>
                            <th>Job No</th>
                            <th>Customer</th>
                            <th>Job Date</th>
                            <th>Due Date</th>
                            <th>Current Stage</th>
                            <th style="width: 160px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($j->job_no); ?></td>
                                <td><?php echo e($j->customer?->full_name); ?></td>
                                <td><?php echo e($j->job_date?->format('d M Y') ?? '-'); ?></td>
                                <td><?php echo e($j->due_date?->format('d M Y') ?? '-'); ?></td>
                                <td><?php echo e($j->currentStage?->name ?? '-'); ?></td>
                                <td>
                                    <a class="btn btn-info btn-sm w-100" href="<?php echo e(route('tailoring.jobs.show', $j)); ?>">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="text-center text-muted">No jobs found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    <?php echo e($jobs->links()); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Tailoring Jobs'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/tailoring/jobs/index.blade.php ENDPATH**/ ?>