@extends('layouts.vertical', ['subtitle' => 'Staff View'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Staff', 'subtitle' => 'View'])

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title mb-0">Staff List</h5>
                <p class="card-subtitle">All staff in your system with details.</p>
            </div>

            <a href="{{ route('staff.create') }}" class="btn btn-primary">
                + Add Staff
            </a>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('staff.index') }}" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                        placeholder="Search by Name / Email">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100" type="submit">Search</button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-outline-secondary w-100" href="{{ route('staff.index') }}">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Staff Code</th>
                            <th>Status</th>
                            <th>Updated At</th>
                            <th style="width: 210px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($staff as $u)
                            @php
                                $role = optional($u->roles->first())->name;
                            @endphp
                            <tr id="staff-{{ $u->id }}">
                                <td>{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>{{ $role ? ucwords(str_replace('_', ' ', $role)) : '-' }}</td>
                                <td>{{ $u->staffProfile?->staff_code ?? '-' }}</td>
                                <td>
                                    @if($u->staffProfile?->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ optional($u->updated_at)->format('d M Y, h:i A') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('staff.edit', $u) }}" class="btn btn-warning btn-sm w-100">
                                            Edit
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm w-100 delete-staff"
                                            data-id="{{ $u->id }}">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No staff found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    {{ $staff->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.delete-staff').forEach(button => {
            button.addEventListener('click', function() {
                let staffId = this.dataset.id;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This staff account will be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    fetch("{{ url('staff') }}/" + staffId, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}",
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
                                document.getElementById('staff-' + staffId)?.remove();
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
@endsection