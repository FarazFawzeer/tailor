

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Customers', 'subtitle' => 'Edit'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <style>
        .required-star {
            color: red;
            font-weight: bold;
            margin-left: 3px;
        }
    </style>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Edit Customer</h5>
            <p class="card-subtitle">Customer Code: <b><?php echo e($customer->customer_code); ?></b></p>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <small class="text-muted d-block mb-3">
                Fields marked with <span class="required-star">*</span> are required.
            </small>

            <form id="updateCustomerForm" action="<?php echo e(route('customers.update', $customer)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="full_name" class="form-label">
                            Full Name <span class="required-star">*</span>
                        </label>
                        <input type="text" id="full_name" name="full_name" class="form-control"
                            value="<?php echo e(old('full_name', $customer->full_name)); ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" id="phone" name="phone" class="form-control"
                            value="<?php echo e(old('phone', $customer->phone)); ?>">
                    </div>
                </div>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control"
                            value="<?php echo e(old('email', $customer->email)); ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nic" class="form-label">NIC</label>
                        <input type="text" id="nic" name="nic" class="form-control"
                            value="<?php echo e(old('nic', $customer->nic)); ?>">
                    </div>
                </div>

                
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea id="address" name="address" class="form-control"
                        rows="2"><?php echo e(old('address', $customer->address)); ?></textarea>
                </div>

                
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea id="notes" name="notes" class="form-control"
                        rows="2"><?php echo e(old('notes', $customer->notes)); ?></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo e(route('customers.index')); ?>" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Update </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('updateCustomerForm').addEventListener('submit', function(e) {
            e.preventDefault();

            let form = this;
            let formData = new FormData(form);

            fetch(form.action, {
                    method: "POST", // Laravel will read _method=PUT
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                        "Accept": "application/json"
                    }
                })
                .then(async response => {
                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            let errors = Object.values(data.errors).flat().join('<br>');
                            document.getElementById('message').innerHTML =
                                `<div class="alert alert-danger">${errors}</div>`;
                            return;
                        }

                        document.getElementById('message').innerHTML =
                            `<div class="alert alert-danger">Something went wrong.</div>`;
                        return;
                    }

                    document.getElementById('message').innerHTML =
                        `<div class="alert alert-success">Customer updated successfully.</div>`;

                    setTimeout(() => {
                        document.getElementById('message').innerHTML = "";
                    }, 3000);
                })
                .catch(error => {
                    document.getElementById('message').innerHTML =
                        `<div class="alert alert-danger">Error: ${error}</div>`;
                });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Customer Edit'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/customers/edit.blade.php ENDPATH**/ ?>