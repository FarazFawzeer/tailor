@extends('layouts.vertical', ['subtitle' => 'Create Job'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Tailoring Jobs', 'subtitle' => 'Create'])

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">New Job</h5>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <form id="createJobForm" action="{{ route('tailoring.jobs.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Customer</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->phone ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Job Date</label>
                        <input type="date" name="job_date" class="form-control" value="{{ now()->toDateString() }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('tailoring.jobs.index') }}" class="btn btn-secondary">Back</a>
                    <button class="btn btn-primary" type="submit">Create Job</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('createJobForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            fetch(this.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                    "Accept": "application/json"
                }
            }).then(async res => {
                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
                        let errors = Object.values(data.errors).flat().join('<br>');
                        document.getElementById('message').innerHTML = `<div class="alert alert-danger">${errors}</div>`;
                        return;
                    }
                    document.getElementById('message').innerHTML = `<div class="alert alert-danger">Something went wrong.</div>`;
                    return;
                }

                document.getElementById('message').innerHTML = `<div class="alert alert-success">${data.message}</div>`;

                // Redirect to job view
                setTimeout(() => {
                    window.location.href = "{{ url('tailoring/jobs') }}/" + data.data.id;
                }, 800);
            });
        });
    </script>
@endsection