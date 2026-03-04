@extends('layouts.vertical', ['subtitle' => 'Hire Item Edit'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Hiring Inventory', 'subtitle' => 'Edit'])

    <style>
        .report-card {
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 14px;
        }

        .muted-help {
            font-size: 12px;
            color: #6c757d;
        }

        .variant-row {
            background: #fff;
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 12px;
        }

        .variant-row:hover {
            border-color: rgba(13, 110, 253, .35);
        }
    </style>

    <div class="card report-card">

        <div class="card-header">
            <h5 class="mb-0">Edit Hire Item</h5>
        </div>

        <div class="card-body">

            <div id="message"></div>

            <form id="updateHireItemForm" action="{{ route('hiring.items.update', $item->id) }}" method="POST"
                enctype="multipart/form-data">

                @csrf
                @method('PUT')

                {{-- =========================
ITEM DETAILS
========================= --}}

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Unique Code *</label>
                        <input name="item_code" class="form-control" value="{{ $item->item_code }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Name *</label>
                        <input name="name" class="form-control" value="{{ $item->name }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <input name="category" class="form-control" value="{{ $item->category }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Default Color</label>
                        <input name="color" class="form-control" value="{{ $item->color }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Hire Price *</label>
                        <input name="hire_price" type="number" step="0.01" class="form-control"
                            value="{{ $item->hire_price }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Deposit</label>
                        <input name="deposit_amount" type="number" step="0.01" class="form-control"
                            value="{{ $item->deposit_amount }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">

                            <option value="available" {{ $item->status == 'available' ? 'selected' : '' }}>
                                Available
                            </option>

                            <option value="reserved" {{ $item->status == 'reserved' ? 'selected' : '' }}>
                                Reserved
                            </option>

                            <option value="hired" {{ $item->status == 'hired' ? 'selected' : '' }}>
                                Hired
                            </option>

                            <option value="maintenance" {{ $item->status == 'maintenance' ? 'selected' : '' }}>
                                Maintenance
                            </option>

                        </select>
                    </div>

                </div>

                {{-- =========================
VARIANTS
========================= --}}

                <div class="mt-4">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6>Sizes & Quantities</h6>

                        <button type="button" class="btn btn-outline-primary btn-sm" id="addVariantBtn">
                            Add Size
                        </button>
                    </div>

                    <div id="variantsWrap" class="d-flex flex-column gap-2">

                        @foreach ($item->variants as $i => $variant)
                            <div class="variant-row p-3" data-variant-row>

                                <div class="row g-2 align-items-end">

                                    <div class="col-md-4">
                                        <label class="form-label">Size *</label>

                                        <input type="text" name="variants[{{ $i }}][size]"
                                            class="form-control" value="{{ $variant->size }}" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Qty *</label>

                                        <input type="number" name="variants[{{ $i }}][qty]"
                                            class="form-control" value="{{ $variant->qty }}" min="0" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Color</label>

                                        <input type="text" name="variants[{{ $i }}][color]"
                                            class="form-control" value="{{ $variant->color }}">
                                    </div>

                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger remove-variant w-100">
                                            Remove
                                        </button>
                                    </div>

                                </div>
                            </div>
                        @endforeach

                    </div>

                </div>

                {{-- =========================
IMAGES
========================= --}}

                <div class="mt-4">

                    <label class="form-label">Upload New Images</label>

                    <input type="file" name="images[]" class="form-control" multiple>

                </div>

                {{-- existing images --}}

                @if ($item->images->count())
                    <div class="row mt-3">

                        @foreach ($item->images as $img)
                            <div class="col-md-2 text-center mb-3">

                                <img src="{{ asset($img->image_path) }}" class="img-fluid rounded mb-1">

                                <button type="button" class="btn btn-sm btn-danger delete-image"
                                    data-url="{{ route('hiring.items.images.destroy', $img->id) }}">
                                    Delete
                                </button>

                            </div>
                        @endforeach

                    </div>
                @endif

                {{-- =========================
NOTES
========================= --}}

                <div class="mt-3">

                    <label class="form-label">Notes</label>

                    <textarea name="notes" class="form-control" rows="2">{{ $item->notes }}</textarea>

                </div>

                <div class="mt-3 form-check form-switch">

                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                        {{ $item->is_active ? 'checked' : '' }}>

                    <label class="form-check-label">
                        Active
                    </label>

                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">

                    <a href="{{ route('hiring.items.index') }}" class="btn btn-secondary" style="width: 120px;">
                        Back
                    </a>

                    <button class="btn btn-primary" style="width: 120px;" type="submit">
                        Update
                    </button>

                </div>

            </form>

        </div>
    </div>

    <script>
        const wrap = document.getElementById('variantsWrap')
        const addBtn = document.getElementById('addVariantBtn')

        function variantRow(i) {

            return `

<div class="variant-row p-3" data-variant-row>

<div class="row g-2 align-items-end">

<div class="col-md-4">

<label class="form-label">Size *</label>

<input name="variants[${i}][size]"
class="form-control"
required>

</div>

<div class="col-md-3">

<label class="form-label">Qty *</label>

<input type="number"
name="variants[${i}][qty]"
class="form-control"
min="0"
value="0"
required>

</div>

<div class="col-md-3">

<label class="form-label">Color</label>

<input name="variants[${i}][color]"
class="form-control">

</div>

<div class="col-md-2">

<button type="button"
class="btn btn-outline-danger remove-variant w-100">

Remove

</button>

</div>

</div>
</div>

`
        }

        addBtn.onclick = function() {

            let i = wrap.querySelectorAll('[data-variant-row]').length

            let div = document.createElement('div')

            div.innerHTML = variantRow(i)

            let row = div.firstElementChild

            row.querySelector('.remove-variant').onclick = () => row.remove()

            wrap.appendChild(row)

        }

        document.querySelectorAll('.remove-variant').forEach(btn => {

            btn.onclick = function() {

                btn.closest('[data-variant-row]').remove()

            }

        })

        document.getElementById('updateHireItemForm')
            .addEventListener('submit', function(e) {

                e.preventDefault()

                const fd = new FormData(this)

                fetch(this.action, {

                        method: 'POST',

                        body: fd,

                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('input[name=_token]').value,
                            "Accept": "application/json"
                        }

                    })

                    .then(async res => {

                        const data = await res.json().catch(() => ({}))

                        const msg = document.getElementById('message')

                        if (!res.ok) {

                            msg.innerHTML =
                                `<div class="alert alert-danger">${data.message||'Error'}</div>`

                            return
                        }

                        msg.innerHTML =
                            `<div class="alert alert-success">${data.message}</div>`

                    })

            })

    document.querySelectorAll('.delete-image').forEach(btn => {
    btn.addEventListener('click', () => {
        const url = btn.dataset.url;
        if (!url) return;

        Swal.fire({
            title: 'Delete image?',
            text: "This cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel'
        }).then(async (r) => {
            if (!r.isConfirmed) return;

            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('input[name=_token]').value,
                        "Accept": "application/json"
                    }
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    Swal.fire('Error', data.message || 'Delete failed', 'error');
                    return;
                }

                // remove card
                btn.closest('.col-md-2')?.remove();
                Swal.fire('Deleted', data.message || 'Image removed', 'success');
            } catch (e) {
                Swal.fire('Error', 'Network error. Please try again.', 'error');
            }
        });
    });
});
    </script>

@endsection
