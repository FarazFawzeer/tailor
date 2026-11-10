@extends('layouts.vertical', ['subtitle' => $user->exists ? 'Edit User' : 'Create User'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Admin', 'subtitle' => $user->exists ? 'Edit User' : 'Create User'])

    @php
        $isSelf = $user->exists && $user->id === auth()->id();
        $currentRole = $user->exists ? $user->getRoleNames()->first() : null;
    @endphp

    <style>
        .required-star { color: red; font-weight: bold; margin-left: 3px; }
    </style>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ $user->exists ? 'Edit User' : 'New User' }}</h5>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <form id="userForm"
                action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @if ($user->exists)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Full Name <span class="required-star">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ $user->name }}"
                            placeholder="Ex: John Doe" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">User Name <span class="required-star">*</span></label>
                        <input type="text" id="email" name="email" class="form-control" value="{{ $user->email }}"
                            placeholder="Ex: john_doe" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">
                            Password @unless ($user->exists)<span class="required-star">*</span>@endunless
                        </label>
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="{{ $user->exists ? 'Leave blank to keep current password' : 'Password' }}"
                            {{ $user->exists ? '' : 'required' }}>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">
                            Re-enter Password @unless ($user->exists)<span class="required-star">*</span>@endunless
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="form-control" placeholder="Re-enter Password" {{ $user->exists ? '' : 'required' }}>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">Role <span class="required-star">*</span></label>
                        @if ($isSelf)
                            <input type="text" class="form-control"
                                value="{{ $currentRole ? \App\Support\Permissions::roleLabel($currentRole) : 'No role' }}"
                                disabled>
                            <div class="form-text">You can't change your own role.</div>
                        @else
                            <select id="role" name="role" class="form-select" required>
                                <option value="">Select Role</option>
                                @foreach ($roles as $r)
                                    <option value="{{ $r }}" {{ $currentRole === $r ? 'selected' : '' }}>
                                        {{ \App\Support\Permissions::roleLabel($r) }}
                                    </option>
                                @endforeach
                            </select>
                            @can('roles.manage')
                                <div class="form-text">
                                    What each role can see is set in
                                    <a href="{{ route('admin.roles.index') }}">Roles &amp; Permissions</a>.
                                </div>
                            @endcan
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="image_path" class="form-label">Profile Image</label>
                        <input type="file" id="image_path" name="image_path" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">{{ $user->exists ? 'Update User' : 'Create User' }}</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('userForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const box = document.getElementById('message');
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
                            (data.message || 'Something went wrong!');
                        box.innerHTML = `<div class="alert alert-danger">${errors}</div>`;
                        return;
                    }

                    Swal.fire({ icon: 'success', title: data.message, timer: 1200, showConfirmButton: false })
                        .then(() => window.location.href = data.redirect);
                })
                .catch(() => {
                    box.innerHTML = `<div class="alert alert-danger">Something went wrong!</div>`;
                });
        });
    </script>
@endsection
