@extends('layouts.vertical', ['subtitle' => 'Workflow Stages View'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Workflow Stages', 'subtitle' => 'View'])

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title mb-0">Workflow Stages</h5>
                <p class="card-subtitle">Manage stage order (Cut → Sewing → Button → Ironing → Packaging).</p>
            </div>
            <a href="{{ route('workflow-stages.create') }}" class="btn btn-primary">+ Add</a>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('workflow-stages.index') }}" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                        placeholder="Search by Code / Name">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-outline-secondary w-100" href="{{ route('workflow-stages.index') }}">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered">
                    <thead class="table-light">
                        <tr>
                            <th>Sort</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Updated At</th>
                            <th style="width: 230px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stages as $s)
                            <tr id="stage-{{ $s->id }}">
                                <td>{{ $s->sort_order }}</td>
                                <td>{{ $s->code }}</td>
                                <td>{{ $s->name }}</td>
                                <td>
                                    @if($s->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $s->updated_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-warning btn-sm w-100" href="{{ route('workflow-stages.edit', $s) }}">Edit</a>
                                        <button type="button" class="btn btn-danger btn-sm w-100 delete-stage" data-id="{{ $s->id }}">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No workflow stages found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    {{ $stages->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.delete-stage').forEach(btn => {
            btn.addEventListener('click', function() {
                let id = this.dataset.id;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This workflow stage will be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    fetch("{{ url('workflow-stages') }}/" + id, {
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
                            document.getElementById('stage-' + id)?.remove();
                            Swal.fire('Deleted!', data.message, 'success');
                        } else {
                            Swal.fire('Error!', data.message || 'Something went wrong!', 'error');
                        }
                    }).catch(() => {
                        Swal.fire('Error!', 'Something went wrong!', 'error');
                    });
                });
            });
        });
    </script>
@endsection