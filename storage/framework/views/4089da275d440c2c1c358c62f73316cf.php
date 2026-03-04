

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Staff', 'subtitle' => 'Create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <style>
        .required-star {
            color: red;
            font-weight: bold;
            margin-left: 3px;
        }
    </style>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">New Staff</h5>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <small class="text-muted d-block mb-3">
                Fields marked with <span class="required-star">*</span> are required.
            </small>

            <form id="createStaffForm" action="<?php echo e(route('staff.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">
                            Full Name <span class="required-star">*</span>
                        </label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo e(old('name')); ?>"
                            placeholder="Ex: Staff Name" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">
                            User Name <span class="required-star">*</span>
                        </label>
                        
                        <input type="text" id="email" name="email" class="form-control" value="<?php echo e(old('email')); ?>"
                            placeholder="Ex: staff_01" required>
                    </div>
                </div>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">
                            Password <span class="required-star">*</span>
                        </label>
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="Password" required autocomplete="new-password">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">
                            Re-enter Password <span class="required-star">*</span>
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="form-control" placeholder="Re-enter Password" required autocomplete="new-password">
                    </div>
                </div>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">
                            Staff Role <span class="required-star">*</span>
                        </label>
                        <select id="role" name="role" class="form-select" required>
                            <option value="">Select Role</option>
                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($r); ?>" <?php echo e(old('role') == $r ? 'selected' : ''); ?>>
                                    <?php echo e(ucwords(str_replace('_', ' ', $r))); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                value="1" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="<?php echo e(old('phone')); ?>"
                            placeholder="Ex: 0771234567">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nic" class="form-label">NIC</label>
                        <input type="text" id="nic" name="nic" class="form-control" value="<?php echo e(old('nic')); ?>"
                            placeholder="Ex: 200012345678">
                    </div>
                </div>

                
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea id="address" name="address" class="form-control" rows="2"
                        placeholder="Staff address"><?php echo e(old('address')); ?></textarea>
                </div>

                
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo e(route('staff.index')); ?>" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Create Staff</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('createStaffForm').addEventListener('submit', function(e) {
            e.preventDefault();

            let form = this;
            let formData = new FormData(form);

            fetch(form.action, {
                    method: "POST",
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
                        `<div class="alert alert-success">${data.message ?? 'Staff created successfully'}</div>`;

                    form.reset();

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
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Staff Create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/staff/create.blade.php ENDPATH**/ ?>