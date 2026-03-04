@extends('layouts.vertical', ['subtitle' => 'Measurement Template Create'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Measurement Templates', 'subtitle' => 'Create'])

    <style>
        .required-star {
            color: red;
            font-weight: bold;
            margin-left: 3px;
        }
    </style>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">New Measurement Template</h5>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <small class="text-muted d-block mb-3">
                Fields marked with <span class="required-star">*</span> are required.
            </small>

            <form id="createTemplateForm" action="{{ route('measurement-templates.store') }}" method="POST">
                @csrf

                {{-- Template details --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Dress Type <span class="required-star">*</span>
                        </label>
                        <select name="dress_type_id" class="form-select" required>
                            <option value="">Select Dress Type</option>
                            @foreach ($dressTypes as $d)
                                <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Template Name <span class="required-star">*</span>
                        </label>
                        <input name="name" class="form-control" placeholder="Ex: Normal / Slim Fit" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <hr>

                {{-- Fields section --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h5 class="mb-0">Measurement Fields</h5>
                        <small class="text-muted">Add chest, waist, sleeve… now (no need to go edit page).</small>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm" id="addRowBtn">
                        + Add Field
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width:180px;">
                                    Label <span class="required-star">*</span>
                                </th>
                                <th style="min-width:160px;">Key (optional)</th>
                                <th style="min-width:120px;">
                                    Unit <span class="required-star">*</span>
                                </th>
                                <th style="min-width:140px;">
                                    Type <span class="required-star">*</span>
                                </th>
                                <th class="text-center" style="min-width:110px;">Required</th>
                                <th style="width:110px;">Sort</th>
                                <th style="width:90px;">Remove</th>
                            </tr>
                        </thead>
                        <tbody id="fieldsBody"></tbody>
                    </table>
                </div>

                <small class="text-muted d-block mb-3">
                    If you add a field row, Label/Unit/Type must be filled.
                </small>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('measurement-templates.index') }}" class="btn btn-secondary">Back</a>
                    <button class="btn btn-primary" type="submit">Create Template</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const fieldsBody = document.getElementById('fieldsBody');
        const addRowBtn = document.getElementById('addRowBtn');

        function newRow(index) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <input class="form-control" name="fields[${index}][label]" placeholder="Ex: Chest" required>
                </td>
                <td>
                    <input class="form-control" name="fields[${index}][key]" placeholder="Ex: chest">
                </td>
                <td>
                    <select class="form-select" name="fields[${index}][unit]" required>
                        <option value="inch">inch</option>
                        <option value="cm">cm</option>
                    </select>
                </td>
                <td>
                    <select class="form-select" name="fields[${index}][input_type]" required>
                        <option value="number">number</option>
                        <option value="text">text</option>
                    </select>
                </td>
                <td class="text-center">
                    <input type="checkbox" name="fields[${index}][is_required]" value="1">
                </td>
                <td>
                    <input type="number" class="form-control" name="fields[${index}][sort_order]" value="0">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                </td>
            `;
            return tr;
        }

        function reindexRows() {
            const rows = [...fieldsBody.querySelectorAll('tr')];
            rows.forEach((row, i) => {
                row.querySelectorAll('input, select').forEach(el => {
                    const name = el.getAttribute('name');
                    if (!name) return;
                    el.setAttribute('name', name.replace(/fields\[\d+\]/, `fields[${i}]`));
                });
            });
        }

        function addRow() {
            const index = fieldsBody.querySelectorAll('tr').length;
            fieldsBody.appendChild(newRow(index));
        }

        // default 1 row
        addRow();

        addRowBtn.addEventListener('click', addRow);

        fieldsBody.addEventListener('click', function(e) {
            const btn = e.target.closest('.remove-row');
            if (!btn) return;

            btn.closest('tr').remove();

            if (fieldsBody.querySelectorAll('tr').length === 0) {
                addRow();
            } else {
                reindexRows();
            }
        });

        document.getElementById('createTemplateForm').addEventListener('submit', function(e) {
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

                this.reset();

                fieldsBody.innerHTML = '';
                addRow();

                setTimeout(() => document.getElementById('message').innerHTML = "", 2500);
            });
        });
    </script>
@endsection