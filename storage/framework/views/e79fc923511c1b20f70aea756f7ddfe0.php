

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Customers', 'subtitle' => 'View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title mb-0">Customer List</h5>
                <p class="card-subtitle">All customers in your system with details.</p>
            </div>

            <a href="<?php echo e(route('customers.create')); ?>" class="btn btn-primary">
                + Add Customer
            </a>
        </div>

        <div class="card-body">

            
            <form method="GET" action="<?php echo e(route('customers.index')); ?>" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" value="<?php echo e(request('q')); ?>"
                        placeholder="Search by Code / Name / Phone / NIC">
                </div>

                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100" type="submit">Search</button>
                </div>

                <div class="col-md-2">
                    <a class="btn btn-outline-secondary w-100" href="<?php echo e(route('customers.index')); ?>">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Code</th>
                            <th scope="col">Full Name</th>
                            <th scope="col">Phone</th>
                            <th scope="col">NIC</th>
                            <th scope="col">Updated At</th>
                            <th scope="col" style="width: 160px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr id="customer-<?php echo e($c->id); ?>">
                                <td><?php echo e($c->customer_code); ?></td>
                                <td><?php echo e($c->full_name); ?></td>
                                <td><?php echo e($c->phone ?? '-'); ?></td>
                                <td><?php echo e($c->nic ?? '-'); ?></td>
                                <td><?php echo e(optional($c->updated_at)->format('d M Y, h:i A')); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="<?php echo e(route('customers.edit', $c)); ?>"
                                            class="btn btn-warning btn-sm w-100">Edit</a>

                                        <button type="button"
                                            class="btn btn-danger btn-sm w-100 delete-customer"
                                            data-id="<?php echo e($c->id); ?>">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No customers found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                
                <div class="d-flex justify-content-end mt-3">
                    <?php echo e($customers->links()); ?>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.delete-customer').forEach(button => {
            button.addEventListener('click', function() {
                let customerId = this.dataset.id;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This customer will be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    fetch("<?php echo e(url('customers')); ?>/" + customerId, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': "<?php echo e(csrf_token()); ?>",
                                'Accept': 'application/json'
                            }
                        })
                        .then(async response => {
                            const data = await response.json().catch(() => ({}));

                            if (!response.ok) {
                                Swal.fire('Error!', data.message || 'Something went wrong!', 'error');
                                return;
                            }

                            if (data.success) {
                                document.getElementById('customer-' + customerId)?.remove();
                                Swal.fire('Deleted!', data.message, 'success');
                            } else {
                                Swal.fire('Error!', data.message || 'Something went wrong!', 'error');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error!', 'Something went wrong!', 'error');
                        });
                });
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Customer View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/customers/index.blade.php ENDPATH**/ ?>