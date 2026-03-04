

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Hiring Inventory', 'subtitle' => 'Create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <style>
        .report-card { border:1px solid rgba(0,0,0,.08); border-radius:14px; }
        .muted-help { font-size: 12px; color: #6c757d; }
        .pill { padding: 2px 10px; border-radius: 999px; font-size: 12px; background: rgba(13,110,253,.08); }
        .variant-row { background: #fff; border: 1px solid rgba(0,0,0,.08); border-radius: 12px; }
        .variant-row:hover { border-color: rgba(13,110,253,.35); }
        .sticky-actions { position: sticky; bottom: 0; background: #fff; padding-top: 10px; }
        .kbd { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size: 12px; }
    </style>

    <div class="card report-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title mb-0">New Hire Item</h5>
                <div class="muted-help mt-1">
                    Add item basic details, then add <span class="pill">Sizes & Qty</span> rows.
                </div>
            </div>
            <div class="d-none d-md-flex gap-2">
                <span class="pill">Step 1: Item</span>
                <span class="pill">Step 2: Sizes & Qty</span>
                <span class="pill">Step 3: Images</span>
            </div>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <form id="createHireItemForm" action="<?php echo e(route('hiring.items.store')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>

                
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="mb-0">Item Details</h6>
                        <small class="muted-help">Fields marked <span class="text-danger">*</span> are required</small>
                    </div>
                    <hr class="mt-2 mb-3">
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Unique Code <span class="text-danger">*</span></label>
                        <input name="item_code" class="form-control" placeholder="Ex: HIRE-SUIT-0001" required>
                        <div class="muted-help mt-1">Must be unique.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input name="name" class="form-control" placeholder="Ex: White Suit" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <input name="category" class="form-control" placeholder="Ex: Suit / Shirt / Kurtha">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Default Color</label>
                        <input name="color" class="form-control" placeholder="Ex: White">
                        <div class="muted-help mt-1">Optional. You can also set color per size row.</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Hire Price <span class="text-danger">*</span></label>
                        <input name="hire_price" type="number" step="0.01" class="form-control" value="0" min="0" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Deposit (optional)</label>
                        <input name="deposit_amount" type="number" step="0.01" class="form-control" value="0" min="0">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="available" selected>Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="hired">Hired</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>

                
                <div class="mt-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0">Sizes & Quantities</h6>
                            <div class="muted-help">Add multiple sizes for this item (each size can have its own qty).</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addVariantBtn">
                                <i class="ti ti-plus me-1"></i>Add Size Row
                            </button>
                        </div>
                    </div>

                    <hr class="mt-2 mb-3">

                    <div id="variantsWrap" class="d-flex flex-column gap-2"></div>

                    <div class="muted-help mt-2">
                        Tip: You can quickly add more rows, then fill size + qty.
                        <span class="kbd">Qty</span> cannot be negative.
                    </div>
                </div>

                
                <div class="mt-4">
                    <h6 class="mb-0">Images</h6>
                    <hr class="mt-2 mb-3">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Images (optional)</label>
                            <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                            <small class="text-muted">You can upload multiple images.</small>
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                                <div class="muted-help">Disable to hide from normal operations.</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any special notes..."></textarea>
                        </div>
                    </div>
                </div>

                
                <div class="sticky-actions mt-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?php echo e(route('hiring.items.index')); ?>" class="btn btn-secondary">Back</a>
                        <button class="btn btn-primary" type="submit">
                            <i class="ti ti-check me-1"></i>Create Item
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // =========================
        // Variants Repeater
        // =========================
        const variantsWrap = document.getElementById('variantsWrap');
        const addVariantBtn = document.getElementById('addVariantBtn');

        function buildVariantRow(index) {
            return `
                <div class="variant-row p-3" data-variant-row="1">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label mb-1">Size <span class="text-danger">*</span></label>
                            <input type="text" name="variants[${index}][size]" class="form-control" placeholder="Ex: L / 42" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label mb-1">Qty <span class="text-danger">*</span></label>
                            <input type="number" name="variants[${index}][qty]" class="form-control" min="0" value="0" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label mb-1">Color (optional)</label>
                            <input type="text" name="variants[${index}][color]" class="form-control" placeholder="Ex: White">
                        </div>

                        <div class="col-md-2 text-end">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-variant">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function renumberVariants() {
            const rows = variantsWrap.querySelectorAll('[data-variant-row]');
            rows.forEach((row, i) => {
                const size = row.querySelector('input[name^="variants"][name$="[size]"]');
                const qty  = row.querySelector('input[name^="variants"][name$="[qty]"]');
                const col  = row.querySelector('input[name^="variants"][name$="[color]"]');

                if (size) size.name = `variants[${i}][size]`;
                if (qty)  qty.name  = `variants[${i}][qty]`;
                if (col)  col.name  = `variants[${i}][color]`;
            });
        }

        function addVariantRow() {
            const index = variantsWrap.querySelectorAll('[data-variant-row]').length;
            const wrapper = document.createElement('div');
            wrapper.innerHTML = buildVariantRow(index);
            const rowEl = wrapper.firstElementChild;

            rowEl.querySelector('.remove-variant').addEventListener('click', () => {
                rowEl.remove();
                renumberVariants();
            });

            variantsWrap.appendChild(rowEl);
        }

        addVariantBtn.addEventListener('click', addVariantRow);

        // Add first row by default
        addVariantRow();

        // =========================
        // AJAX Submit
        // =========================
        document.getElementById('createHireItemForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const msg = document.getElementById('message');

            // Basic guard: at least 1 variant row must exist
            if (variantsWrap.querySelectorAll('[data-variant-row]').length === 0) {
                msg.innerHTML = `<div class="alert alert-danger">Please add at least one size & quantity row.</div>`;
                return;
            }

            const fd = new FormData(form);

            fetch(form.action, {
                method: "POST",
                body: fd,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                    "Accept": "application/json"
                }
            }).then(async res => {
                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
                        msg.innerHTML = `<div class="alert alert-danger">${Object.values(data.errors).flat().join('<br>')}</div>`;
                        return;
                    }
                    msg.innerHTML = `<div class="alert alert-danger">${data.message || 'Something went wrong.'}</div>`;
                    return;
                }

                msg.innerHTML = `<div class="alert alert-success">${data.message || 'Hire item created successfully.'}</div>`;
                form.reset();

                // reset variants
                variantsWrap.innerHTML = '';
                addVariantRow();

                setTimeout(() => msg.innerHTML = "", 3000);
            }).catch(() => {
                msg.innerHTML = `<div class="alert alert-danger">Network error. Please try again.</div>`;
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Hire Item Create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\tailor\resources\views/hiring/items/create.blade.php ENDPATH**/ ?>