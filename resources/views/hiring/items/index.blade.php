@extends('layouts.vertical', ['subtitle' => 'Hire Items'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Hiring Inventory', 'subtitle' => 'View'])

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Hire Items</h5>
            <p class="card-subtitle mb-0">Manage hire dress inventory with unique codes and images.</p>
        </div>

        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input name="q" class="form-control" value="{{ $q ?? '' }}" placeholder="Search code / name / category">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        @foreach(['available','reserved','hired','maintenance'] as $st)
                            <option value="{{ $st }}" {{ ($status ?? '') === $st ? 'selected' : '' }}>
                                {{ ucfirst($st) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-outline-dark w-100">Go</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('hiring.items.create') }}" class="btn btn-primary w-100">Create</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Code</th>
                            <th>Category</th>
                            <th>Size/Color</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th width="220">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $it)
                            <tr id="row-{{ $it->id }}">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img class="avatar-sm rounded"
                                             src="{{ $it->images->first()?->image_path ? asset($it->images->first()->image_path) : asset('/images/users/avatar-6.jpg') }}"
                                             alt="img">
                                        <div>
                                            <div class="fw-bold">{{ $it->name }}</div>
                                            <div class="text-muted small">{{ $it->notes ? \Illuminate\Support\Str::limit($it->notes, 40) : '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><b>{{ $it->item_code }}</b></td>
                                <td>{{ $it->category ?? '-' }}</td>
                                <td>{{ $it->size ?? '-' }} / {{ $it->color ?? '-' }}</td>
                                <td>{{ number_format((float)$it->hire_price, 2) }}</td>
                                <td><span class="badge bg-info">{{ ucfirst($it->status) }}</span></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('hiring.items.edit', $it) }}" class="btn btn-outline-dark btn-sm w-100">Edit</a>
                                        <button class="btn btn-danger btn-sm w-100 btn-delete" data-id="{{ $it->id }}">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">No items found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $items->links() }}
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;

                Swal.fire({
                    title: 'Delete this item?',
                    text: "You can't undo this.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete'
                }).then((r) => {
                    if (!r.isConfirmed) return;

                    fetch("{{ url('hiring/items') }}/" + id, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        }
                    }).then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            Swal.fire('Error', data.message || 'Something went wrong', 'error');
                            return;
                        }
                        document.getElementById('row-' + id)?.remove();
                        Swal.fire('Deleted', data.message, 'success');
                    });
                });
            });
        });
    </script>
@endsection