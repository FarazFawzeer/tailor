@extends('layouts.vertical', ['subtitle' => 'Customer View'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Customers', 'subtitle' => 'View'])

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title mb-0">Customer List</h5>
                <p class="card-subtitle">All customers in your system with details.</p>
            </div>

            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                + Add Customer
            </a>
        </div>

        <div class="card-body">

            {{-- Search --}}
            <form method="GET" action="{{ route('customers.index') }}" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                        placeholder="Search by Code / Name / Phone / NIC">
                </div>

                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100" type="submit">Search</button>
                </div>

                <div class="col-md-2">
                    <a class="btn btn-outline-secondary w-100" href="{{ route('customers.index') }}">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Code</th>
                            <th scope="col">Full Name</th>
                            <th scope="col">Phone</th>
                            <th scope="col">NIC</th>
                            <th scope="col">Updated At</th>
                            <th scope="col" style="width: 160px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($customers as $c)
                            <tr id="customer-{{ $c->id }}">
                                <td>{{ $c->customer_code }}</td>
                                <td>{{ $c->full_name }}</td>
                                <td>{{ $c->phone ?? '-' }}</td>
                                <td>{{ $c->nic ?? '-' }}</td>
                                <td>{{ optional($c->updated_at)->format('d M Y, h:i A') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('customers.edit', $c) }}"
                                            class="btn btn-warning btn-sm w-100">Edit</a>

                                        <button type="button"
                                            class="btn btn-danger btn-sm w-100 delete-customer"
                                            data-id="{{ $c->id }}">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No customers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.delete-customer').forEach(button => {
            button.addEventListener('click', function() {
                let customerId = this.dataset.id;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This customer will be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    fetch("{{ url('customers') }}/" + customerId, {
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
                                document.getElementById('customer-' + customerId)?.remove();
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