@extends('layouts.vertical', ['subtitle' => 'Users'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Admin', 'subtitle' => 'Users'])

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title mb-0">Users</h5>
                <p class="card-subtitle mb-0">Everyone who can log in, and the role that decides what they can see.</p>
            </div>
            @can('users.create')
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Add User</a>
            @endcan
        </div>

        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-warning">{{ session('error') }}</div>
            @endif

            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" name="q" class="form-control" value="{{ $q }}"
                        placeholder="Search by Name / User Name">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="">All roles</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r }}" {{ $roleFilter === $r ? 'selected' : '' }}>
                                {{ \App\Support\Permissions::roleLabel($r) }}
                            </option>
                        @endforeach
                        <option value="__none" {{ $roleFilter === '__none' ? 'selected' : '' }}>No role</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100" type="submit">Search</button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-outline-secondary w-100" href="{{ route('admin.users.index') }}">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>User Name</th>
                            <th>Role</th>
                            <th>Updated At</th>
                            <th style="width: 200px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php
                                $isSuper = $user->hasRole(\App\Support\Permissions::SUPER_ADMIN);
                                $canTouch = !$isSuper || auth()->user()->hasRole(\App\Support\Permissions::SUPER_ADMIN);
                            @endphp
                            <tr id="user-{{ $user->id }}">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $user->image_path ? asset($user->image_path) : asset('/images/users/avatar-6.jpg') }}"
                                            alt="{{ $user->name }}" class="avatar-sm rounded-circle">
                                        <h6 class="mb-0">{{ $user->name }}</h6>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @forelse ($user->roles as $r)
                                        <span class="badge bg-{{ $r->name === \App\Support\Permissions::SUPER_ADMIN ? 'success' : 'info' }}">
                                            {{ \App\Support\Permissions::roleLabel($r->name) }}
                                        </span>
                                    @empty
                                        <span class="badge bg-danger">No role (no access)</span>
                                    @endforelse
                                </td>
                                <td>{{ $user->updated_at?->format('d M Y, h:i A') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @if ($canTouch)
                                            @can('users.edit')
                                                <a href="{{ route('admin.users.edit', $user) }}"
                                                    class="btn btn-outline-primary btn-sm w-100">Edit</a>
                                            @endcan
                                            @can('users.delete')
                                                @if ($user->id !== auth()->id())
                                                    <button type="button" class="btn btn-outline-danger btn-sm w-100 delete-user"
                                                        data-id="{{ $user->id }}">Delete</button>
                                                @endif
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.delete-user').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.dataset.id;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch("{{ url('admin/users') }}/" + userId, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('user-' + userId)?.remove();
                                Swal.fire('Deleted!', data.message, 'success');
                            } else {
                                Swal.fire('Error!', data.message || 'Something went wrong!', 'error');
                            }
                        })
                        .catch(() => Swal.fire('Error!', 'Something went wrong!', 'error'));
                });
            });
        });
    </script>
@endsection
