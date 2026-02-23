@extends('layouts.vertical', ['subtitle' => 'Dress Types View'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Dress Types', 'subtitle' => 'View'])

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title mb-0">Dress Type List</h5>
                <p class="card-subtitle">All dress types with diagrams and status.</p>
            </div>

            <a href="{{ route('dress-types.create') }}" class="btn btn-primary">+ Create</a>
        </div>

        <div class="card-body">
            <div id="message"></div>

            {{-- Search --}}
            <form method="GET" action="{{ route('dress-types.index') }}" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" value="{{ $q ?? '' }}"
                        placeholder="Search by code or name">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-outline-secondary w-100" href="{{ route('dress-types.index') }}">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Front</th>
                            <th>Back</th>
                            <th>Status</th>
                            <th>Updated At</th>
                            <th style="width: 220px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($dressTypes as $dt)
                            <tr id="dressType-{{ $dt->id }}">
                                <td><b>{{ $dt->code }}</b></td>
                                <td>{{ $dt->name }}</td>

                                {{-- Front thumbnail --}}
                                <td>
                                    @if($dt->diagram_front)
                                        <a href="{{ asset($dt->diagram_front) }}" target="_blank">
                                            <img src="{{ asset($dt->diagram_front) }}"
                                                alt="Front"
                                                class="rounded border"
                                                style="width: 60px; height: 60px; object-fit: cover;">
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                {{-- Back thumbnail --}}
                                <td>
                                    @if($dt->diagram_back)
                                        <a href="{{ asset($dt->diagram_back) }}" target="_blank">
                                            <img src="{{ asset($dt->diagram_back) }}"
                                                alt="Back"
                                                class="rounded border"
                                                style="width: 60px; height: 60px; object-fit: cover;">
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    @if($dt->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>

                                <td>{{ $dt->updated_at?->format('d M Y, h:i A') ?? '-' }}</td>

                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('dress-types.edit', $dt) }}" class="btn btn-info btn-sm w-100">
                                            Edit
                                        </a>

                                        <button type="button" class="btn btn-danger btn-sm w-100 delete-dressType"
                                            data-id="{{ $dt->id }}">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No dress types found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $dressTypes->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.delete-dressType').forEach(button => {
            button.addEventListener('click', function() {
                let id = this.dataset.id;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This dress type will be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    fetch("{{ url('dress-types') }}/" + id, {
                            method: "DELETE",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Accept": "application/json"
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('dressType-' + id)?.remove();
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