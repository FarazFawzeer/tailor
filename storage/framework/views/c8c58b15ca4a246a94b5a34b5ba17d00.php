

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Tailoring Jobs', 'subtitle' => 'View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Job: <?php echo e($job->job_no); ?></h5>
            <p class="card-subtitle mb-0">
                Customer: <b><?php echo e($job->customer?->full_name); ?></b> |
                Stage: <b><?php echo e($job->currentStage?->name ?? '-'); ?></b>
            </p>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><b>Job Date:</b> <?php echo e($job->job_date?->format('d M Y') ?? '-'); ?></div>
                <div class="col-md-3"><b>Due Date:</b> <?php echo e($job->due_date?->format('d M Y') ?? '-'); ?></div>
                <div class="col-md-6"><b>Notes:</b> <?php echo e($job->notes ?? '-'); ?></div>
            </div>
        </div>
    </div>

    
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Create New Batch</h5>
        </div>
        <div class="card-body">
            <div id="batchMessage"></div>

            <form id="createBatchForm">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Batch Date</label>
                        <input type="date" name="batch_date" class="form-control" value="<?php echo e(now()->toDateString()); ?>">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="<?php echo e($job->due_date?->toDateString()); ?>">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Optional">
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">+ Create Batch</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="batchesArea">
        <?php $__empty_1 = true; $__currentLoopData = $job->batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php echo $__env->make('tailoring.jobs.partials.batch_card', ['job' => $job, 'batch' => $batch], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="card">
                <div class="card-body text-center text-muted">No batches yet. Create first batch.</div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Create Batch
        document.getElementById('createBatchForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch("<?php echo e(route('tailoring.jobs.batches.store', $job)); ?>", {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>",
                    "Accept": "application/json"
                }
            }).then(async res => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    document.getElementById('batchMessage').innerHTML =
                        `<div class="alert alert-danger">Error creating batch</div>`;
                    return;
                }

                document.getElementById('batchMessage').innerHTML =
                    `<div class="alert alert-success">${data.message}</div>`;
                setTimeout(() => document.getElementById('batchMessage').innerHTML = "", 2000);

                // Reload page (simple for now)
                setTimeout(() => window.location.reload(), 600);
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Job View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/tailoring/jobs/show.blade.php ENDPATH**/ ?>