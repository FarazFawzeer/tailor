@extends('layouts.vertical', ['subtitle' => 'Measurement Template Edit'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Measurement Templates', 'subtitle' => 'Edit'])

    <style>
        .required-star {
            color: red;
            font-weight: bold;
            margin-left: 3px;
        }
    </style>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Edit Template</h5>
            <p class="card-subtitle">
                Dress: <b>{{ $template->dressType?->name }}</b> | Template: <b>{{ $template->name }}</b>
            </p>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <small class="text-muted d-block mb-3">
                Fields marked with <span class="required-star">*</span> are required.
            </small>

            <form id="updateTemplateForm" action="{{ route('measurement-templates.update', $template) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Dress Type <span class="required-star">*</span>
                        </label>
                        <select name="dress_type_id" class="form-select" required>
                            @foreach ($dressTypes as $d)
                                <option value="{{ $d->id }}"
                                    {{ $template->dress_type_id == $d->id ? 'selected' : '' }}>
                                    {{ $d->name }} ({{ $d->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Template Name <span class="required-star">*</span>
                        </label>
                        <input name="name" class="form-control" value="{{ $template->name }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ $template->notes }}</textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                            {{ $template->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('measurement-templates.index') }}" class="btn btn-secondary">Back</a>
                    <button class="btn btn-primary" type="submit">Update Template</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Fields --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Template Fields</h5>
            <p class="card-subtitle">Add/update measurement fields (Chest, Waist, Sleeve...).</p>
        </div>

        <div class="card-body">
            <div id="fieldMessage"></div>

            <small class="text-muted d-block mb-3">
                Fields marked with <span class="required-star">*</span> are required.
            </small>

            {{-- Add new field --}}
            <form id="addFieldForm" class="mb-3">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="form-label">
                            Label <span class="required-star">*</span>
                        </label>
                        <input name="label" class="form-control" placeholder="Ex: Chest" required>
                    </div>

                    <div class="col-md-3 mb-2">
                        <label class="form-label">Key (optional)</label>
                        <input name="key" class="form-control" placeholder="Ex: chest">
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label">
                            Unit <span class="required-star">*</span>
                        </label>
                        <select name="unit" class="form-select" required>
                            <option value="inch">inch</option>
                            <option value="cm">cm</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label">
                            Type <span class="required-star">*</span>
                        </label>
                        <select name="input_type" class="form-select" required>
                            <option value="number">number</option>
                            <option value="text">text</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label">Sort</label>
                        <input name="sort_order" type="number" class="form-control" value="0">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1">
                        <label class="form-check-label" for="is_required">Required</label>
                    </div>
                    <button class="btn btn-primary" type="submit">+ Add Field</button>
                </div>
            </form>

            {{-- List fields --}}
            <div class="table-responsive">
                <table class="table table-hover table-centered">
                    <thead class="table-light">
                        <tr>
                            <th>Label</th>
                            <th>Key</th>
                            <th>Unit</th>
                            <th>Type</th>
                            <th>Required</th>
                            <th>Sort</th>
                            <th style="width: 220px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="fieldsTableBody">
                        @forelse($template->fields as $f)
                            <tr id="field-{{ $f->id }}">
                                <td><input class="form-control form-control-sm" value="{{ $f->label }}"
                                        data-field="label" data-id="{{ $f->id }}"></td>
                                <td><input class="form-control form-control-sm" value="{{ $f->key }}"
                                        data-field="key" data-id="{{ $f->id }}"></td>
                                <td>
                                    <select class="form-select form-select-sm" data-field="unit"
                                        data-id="{{ $f->id }}">
                                        <option value="inch" {{ $f->unit == 'inch' ? 'selected' : '' }}>inch</option>
                                        <option value="cm" {{ $f->unit == 'cm' ? 'selected' : '' }}>cm</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm" data-field="input_type"
                                        data-id="{{ $f->id }}">
                                        <option value="number" {{ $f->input_type == 'number' ? 'selected' : '' }}>number
                                        </option>
                                        <option value="text" {{ $f->input_type == 'text' ? 'selected' : '' }}>text</option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" data-field="is_required" data-id="{{ $f->id }}"
                                        {{ $f->is_required ? 'checked' : '' }}>
                                </td>
                                <td><input type="number" class="form-control form-control-sm"
                                        value="{{ $f->sort_order }}" data-field="sort_order"
                                        data-id="{{ $f->id }}"></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-success btn-sm w-100 save-field"
                                            data-id="{{ $f->id }}">Save</button>
                                        <button class="btn btn-danger btn-sm w-100 delete-field"
                                            data-id="{{ $f->id }}">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="noFieldsRow">
                                <td colspan="7" class="text-center text-muted">No fields added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script>
        // ---- Template update ----
        document.getElementById('updateTemplateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            fetch(this.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                    "Accept": "application/json"
                }
            }).then(async res => {
                let data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
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
                    `<div class="alert alert-success">${data.message}</div>`;
                setTimeout(() => document.getElementById('message').innerHTML = "", 2500);
            });
        });

        // ---- Add field ----
        document.getElementById('addFieldForm').addEventListener('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            fetch("{{ route('measurement-templates.fields.store', $template) }}", {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            }).then(async res => {
                let data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
                        let errors = Object.values(data.errors).flat().join('<br>');
                        document.getElementById('fieldMessage').innerHTML =
                            `<div class="alert alert-danger">${errors}</div>`;
                        return;
                    }
                    document.getElementById('fieldMessage').innerHTML =
                        `<div class="alert alert-danger">Something went wrong.</div>`;
                    return;
                }

                document.getElementById('fieldMessage').innerHTML =
                    `<div class="alert alert-success">${data.message}</div>`;
                setTimeout(() => document.getElementById('fieldMessage').innerHTML = "", 2000);

                // Remove no fields row if exists
                document.getElementById('noFieldsRow')?.remove();

                const f = data.data;

                const row = document.createElement('tr');
                row.id = `field-${f.id}`;
                row.innerHTML = `
                    <td><input class="form-control form-control-sm" value="${f.label}" data-field="label" data-id="${f.id}"></td>
                    <td><input class="form-control form-control-sm" value="${f.key}" data-field="key" data-id="${f.id}"></td>
                    <td>
                        <select class="form-select form-select-sm" data-field="unit" data-id="${f.id}">
                            <option value="inch" ${f.unit === 'inch' ? 'selected' : ''}>inch</option>
                            <option value="cm" ${f.unit === 'cm' ? 'selected' : ''}>cm</option>
                        </select>
                    </td>
                    <td>
                        <select class="form-select form-select-sm" data-field="input_type" data-id="${f.id}">
                            <option value="number" ${f.input_type === 'number' ? 'selected' : ''}>number</option>
                            <option value="text" ${f.input_type === 'text' ? 'selected' : ''}>text</option>
                        </select>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" data-field="is_required" data-id="${f.id}" ${f.is_required ? 'checked' : ''}>
                    </td>
                    <td><input type="number" class="form-control form-control-sm" value="${f.sort_order}" data-field="sort_order" data-id="${f.id}"></td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success btn-sm w-100 save-field" data-id="${f.id}">Save</button>
                            <button class="btn btn-danger btn-sm w-100 delete-field" data-id="${f.id}">Delete</button>
                        </div>
                    </td>
                `;
                document.getElementById('fieldsTableBody').prepend(row);

                // reset add form
                this.reset();
            });
        });

        // ---- Save field / Delete field (event delegation) ----
        document.getElementById('fieldsTableBody').addEventListener('click', function(e) {
            const saveBtn = e.target.closest('.save-field');
            const deleteBtn = e.target.closest('.delete-field');

            if (saveBtn) {
                const id = saveBtn.dataset.id;
                const row = document.getElementById('field-' + id);

                const payload = {
                    label: row.querySelector('[data-field="label"]').value,
                    key: row.querySelector('[data-field="key"]').value,
                    unit: row.querySelector('[data-field="unit"]').value,
                    input_type: row.querySelector('[data-field="input_type"]').value,
                    is_required: row.querySelector('[data-field="is_required"]').checked ? 1 : 0,
                    sort_order: row.querySelector('[data-field="sort_order"]').value || 0
                };

                fetch("{{ url('measurement-templates/' . $template->id . '/fields') }}/" + id, {
                    method: "PUT",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(payload)
                }).then(async res => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        Swal.fire('Error!', data.message || 'Validation error!', 'error');
                        return;
                    }
                    Swal.fire('Saved!', data.message, 'success');
                });

                return;
            }

            if (deleteBtn) {
                const id = deleteBtn.dataset.id;

                Swal.fire({
                    title: 'Delete field?',
                    text: "This field will be removed from template!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    fetch("{{ url('measurement-templates/' . $template->id . '/fields') }}/" + id, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        }
                    }).then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            Swal.fire('Error!', data.message || 'Something went wrong!',
                                'error');
                            return;
                        }
                        document.getElementById('field-' + id)?.remove();
                        Swal.fire('Deleted!', data.message, 'success');
                    });
                });

                return;
            }
        });
    </script>
@endsection
