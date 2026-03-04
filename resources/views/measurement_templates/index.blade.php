@extends('layouts.vertical', ['subtitle' => 'Measurement Templates View'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Measurement Templates', 'subtitle' => 'View'])

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title mb-0">Measurement Templates</h5>
                <p class="card-subtitle">Templates for each dress type (fields inside template).</p>
            </div>
            <a href="{{ route('measurement-templates.create') }}" class="btn btn-primary">+ Add Template</a>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('measurement-templates.index') }}" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                        placeholder="Search by Template Name / Dress Type">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-outline-secondary w-100" href="{{ route('measurement-templates.index') }}">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered">
                    <thead class="table-light">
                        <tr>
                            <th>Dress Type</th>
                            <th>Template Name</th>
                            <th>Status</th>
                            <th>Updated At</th>
                            <th style="width: 230px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $t)
                            <tr id="template-{{ $t->id }}">
                                <td>{{ $t->dressType?->name }} ({{ $t->dressType?->code }})</td>
                                <td>{{ $t->name }}</td>
                                <td>
                                    @if($t->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $t->updated_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-warning btn-sm w-100"
                                            href="{{ route('measurement-templates.edit', $t) }}">Edit</a>
                                        <button class="btn btn-danger btn-sm w-100 delete-template"
                                            data-id="{{ $t->id }}">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No templates found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    {{ $templates->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.delete-template').forEach(btn => {
            btn.addEventListener('click', function() {
                let id = this.dataset.id;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This template and its fields will be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    fetch("{{ url('measurement-templates') }}/" + id, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        }
                    }).then(async res => {
                        let data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            Swal.fire('Error!', data.message || 'Something went wrong!', 'error');
                            return;
                        }
                        if (data.success) {
                            document.getElementById('template-' + id)?.remove();
                            Swal.fire('Deleted!', data.message, 'success');
                        } else {
                            Swal.fire('Error!', data.message || 'Something went wrong!', 'error');
                        }
                    });
                });
            });
        });
    </script>
@endsection