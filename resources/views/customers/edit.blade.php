@extends('layouts.vertical', ['subtitle' => 'Customer Edit'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Customers', 'subtitle' => 'Edit'])

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Edit Customer</h5>
            <p class="card-subtitle">Customer Code: <b>{{ $customer->customer_code }}</b></p>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <form id="updateCustomerForm" action="{{ route('customers.update', $customer) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Name + Phone --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control"
                            value="{{ old('full_name', $customer->full_name) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" id="phone" name="phone" class="form-control"
                            value="{{ old('phone', $customer->phone) }}">
                    </div>
                </div>

                {{-- Email + NIC --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control"
                            value="{{ old('email', $customer->email) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nic" class="form-label">NIC</label>
                        <input type="text" id="nic" name="nic" class="form-control"
                            value="{{ old('nic', $customer->nic) }}">
                    </div>
                </div>

                {{-- Address --}}
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea id="address" name="address" class="form-control"
                        rows="2">{{ old('address', $customer->address) }}</textarea>
                </div>

                {{-- Notes --}}
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea id="notes" name="notes" class="form-control"
                        rows="2">{{ old('notes', $customer->notes) }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('customers.index') }}" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Update Customer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('updateCustomerForm').addEventListener('submit', function(e) {
            e.preventDefault();

            let form = this;
            let formData = new FormData(form);

            fetch(form.action, {
                    method: "POST", // Laravel will read _method=PUT
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
                        `<div class="alert alert-success">Customer updated successfully.</div>`;

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