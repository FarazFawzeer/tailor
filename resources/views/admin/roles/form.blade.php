@extends('layouts.vertical', ['subtitle' => $role->exists ? 'Edit Role' : 'Create Role'])

@section('content')
    @include('layouts.partials.page-title', [
        'title' => 'Roles & Permissions',
        'subtitle' => $role->exists ? 'Edit Role' : 'Create Role',
    ])

    <style>
        .required-star { color: red; font-weight: bold; margin-left: 3px; }
        .perm-table td, .perm-table th { vertical-align: middle; }
        .perm-table .module-col { width: 240px; }
        .perm-actions { display: flex; flex-wrap: wrap; gap: 6px 18px; }
        .perm-actions .form-check { margin: 0; }
    </style>

    <form id="roleForm"
        action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}"
        method="POST">
        @csrf
        @if ($role->exists)
            @method('PUT')
        @endif

        <div class="card">
            <div class="card-body">
                <div id="message"></div>

                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label for="name" class="form-label">Role Name <span class="required-star">*</span></label>
                        <input type="text" id="name" name="name" class="form-control"
                            value="{{ $role->exists ? \App\Support\Permissions::roleLabel($role->name) : '' }}"
                            placeholder="Ex: Shop Manager" required>
                        <div class="form-text">Saved as a short code, ex: "Shop Manager" → shop_manager</div>
                    </div>
                    <div class="col-md-6 mb-2 d-flex align-items-end justify-content-md-end gap-2">
                        <button type="button" class="btn btn-outline-success btn-sm" id="btnSelectAll">Select all</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnClearAll">Clear all</button>
                    </div>
                </div>
            </div>
        </div>

        @foreach ($sections as $sectionName => $modules)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ $sectionName }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 perm-table">
                            <tbody>
                                @foreach ($modules as $module => $def)
                                    <tr class="perm-row">
                                        <th class="module-col">
                                            <div class="form-check">
                                                <input class="form-check-input rowToggle" type="checkbox"
                                                    id="row_{{ $module }}">
                                                <label class="form-check-label fw-semibold" for="row_{{ $module }}">
                                                    {{ $def['label'] }}
                                                </label>
                                            </div>
                                        </th>
                                        <td>
                                            <div class="perm-actions">
                                                @foreach ($def['actions'] as $action => $label)
                                                    @php $perm = "{$module}.{$action}"; @endphp
                                                    <div class="form-check">
                                                        <input class="form-check-input permCheck" type="checkbox"
                                                            name="permissions[]" value="{{ $perm }}"
                                                            id="perm_{{ $module }}_{{ $action }}"
                                                            {{ in_array($perm, $selected, true) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="perm_{{ $module }}_{{ $action }}">
                                                            {{ $label }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-primary">{{ $role->exists ? 'Update Role' : 'Create Role' }}</button>
        </div>
    </form>

    <script>
        (function() {
            const form = document.getElementById('roleForm');
            const box = document.getElementById('message');

            function syncRow(row) {
                const checks = row.querySelectorAll('.permCheck');
                const checked = row.querySelectorAll('.permCheck:checked').length;
                const toggle = row.querySelector('.rowToggle');
                toggle.checked = checked === checks.length;
                toggle.indeterminate = checked > 0 && checked < checks.length;
            }

            document.querySelectorAll('.perm-row').forEach(row => {
                syncRow(row);

                row.querySelector('.rowToggle').addEventListener('change', function() {
                    row.querySelectorAll('.permCheck').forEach(c => c.checked = this.checked);
                    syncRow(row);
                });

                row.querySelectorAll('.permCheck').forEach(c => c.addEventListener('change', function() {
                    // ticking create/edit/delete etc. without "view" makes no sense: tick view too
                    if (this.checked && !this.value.endsWith('.view')) {
                        const view = row.querySelector('.permCheck[value$=".view"]');
                        if (view) view.checked = true;
                    }
                    syncRow(row);
                }));
            });

            function setAll(on) {
                document.querySelectorAll('.permCheck').forEach(c => c.checked = on);
                document.querySelectorAll('.perm-row').forEach(syncRow);
            }
            document.getElementById('btnSelectAll').addEventListener('click', () => setAll(true));
            document.getElementById('btnClearAll').addEventListener('click', () => setAll(false));

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                box.innerHTML = '';

                fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json'
                        }
                    })
                    .then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok || !data.success) {
                            const errors = data.errors ? Object.values(data.errors).flat().join('<br>') :
                                (data.message || 'Something went wrong');
                            box.innerHTML = `<div class="alert alert-danger">${errors}</div>`;
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            return;
                        }

                        Swal.fire({ icon: 'success', title: data.message, timer: 1200, showConfirmButton: false })
                            .then(() => window.location.href = data.redirect);
                    })
                    .catch(() => {
                        box.innerHTML = `<div class="alert alert-danger">Something went wrong</div>`;
                    });
            });
        })();
    </script>
@endsection
