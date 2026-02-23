@extends('layouts.vertical', ['subtitle' => 'Staff Edit'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Staff', 'subtitle' => 'Edit'])

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Edit Staff</h5>
            <p class="card-subtitle">Update staff account details.</p>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <form id="updateStaffForm" action="{{ route('staff.update', $staff) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Name + Email --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                            value="{{ old('name', $staff->name) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control"
                            value="{{ old('email', $staff->email) }}" required>
                    </div>
                </div>

                {{-- Password + Confirm (optional) --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">New Password (optional)</label>
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="Leave empty to keep current password">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Re-enter Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="form-control" placeholder="Re-enter Password">
                    </div>
                </div>

                {{-- Role + Active --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">Staff Role</label>
                        <select id="role" name="role" class="form-select" required>
                            <option value="">Select Role</option>
                            @php
                                $currentRole = optional($staff->roles->first())->name;
                            @endphp
                            @foreach($roles as $r)
                                <option value="{{ $r }}" {{ old('role', $currentRole) == $r ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $r)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                {{ old('is_active', $staff->staffProfile?->is_active ? 1 : 0) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                {{-- Phone + NIC --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" id="phone" name="phone" class="form-control"
                            value="{{ old('phone', $staff->staffProfile?->phone) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nic" class="form-label">NIC</label>
                        <input type="text" id="nic" name="nic" class="form-control"
                            value="{{ old('nic', $staff->staffProfile?->nic) }}">
                    </div>
                </div>

                {{-- Address --}}
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea id="address" name="address" class="form-control" rows="2">{{ old('address', $staff->staffProfile?->address) }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('staff.index') }}" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Update Staff</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('updateStaffForm').addEventListener('submit', function(e) {
            e.preventDefault();

            let form = this;
            let formData = new FormData(form);

            fetch(form.action, {
                    method: "POST", // Laravel reads _method=PUT
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
                        `<div class="alert alert-success">${data.message ?? 'Staff updated successfully'}</div>`;

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
@endsection