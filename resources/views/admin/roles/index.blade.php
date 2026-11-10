@extends('layouts.vertical', ['subtitle' => 'Roles & Permissions'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Admin', 'subtitle' => 'Roles & Permissions'])

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title mb-0">Roles</h5>
                <p class="card-subtitle mb-0">Each role decides which screens and actions its users can see.</p>
            </div>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">+ Add Role</a>
        </div>

        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-warning">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover table-centered">
                    <thead class="table-light">
                        <tr>
                            <th>Role</th>
                            <th>Permissions</th>
                            <th>Users</th>
                            <th style="width: 220px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            @php $isSuper = $role->name === \App\Support\Permissions::SUPER_ADMIN; @endphp
                            <tr id="role-{{ $role->id }}">
                                <td>
                                    <b>{{ \App\Support\Permissions::roleLabel($role->name) }}</b>
                                    <div class="text-muted small">{{ $role->name }}</div>
                                </td>
                                <td>
                                    @if ($isSuper)
                                        <span class="badge bg-success">All permissions</span>
                                    @else
                                        <span class="badge bg-{{ $role->permissions_count ? 'info' : 'secondary' }}">
                                            {{ $role->permissions_count }} / {{ $totalPermissions }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $role->users_count }}</td>
                                <td>
                                    @if ($isSuper)
                                        <span class="text-muted small">Built-in, cannot be changed</span>
                                    @else
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.roles.edit', $role) }}"
                                                class="btn btn-outline-primary btn-sm w-100">Edit</a>
                                            <button type="button" class="btn btn-outline-danger btn-sm w-100 btnDeleteRole"
                                                data-id="{{ $role->id }}"
                                                data-name="{{ \App\Support\Permissions::roleLabel($role->name) }}">
                                                Delete
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.btnDeleteRole').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;

                Swal.fire({
                    title: 'Delete role?',
                    text: `Delete the "${this.dataset.name}" role?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch("{{ url('admin/roles') }}/" + id, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('role-' + id)?.remove();
                                Swal.fire('Deleted', data.message, 'success');
                            } else {
                                Swal.fire('Cannot delete', data.message || 'Something went wrong', 'error');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Something went wrong', 'error'));
                });
            });
        });
    </script>
@endsection
