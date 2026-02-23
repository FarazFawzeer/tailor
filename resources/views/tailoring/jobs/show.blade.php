@extends('layouts.vertical', ['subtitle' => 'Job View'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Tailoring Jobs', 'subtitle' => 'View'])

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Job: {{ $job->job_no }}</h5>
            <p class="card-subtitle mb-0">
                Customer: <b>{{ $job->customer?->full_name }}</b> |
                Stage: <b>{{ $job->currentStage?->name ?? '-' }}</b>
            </p>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><b>Job Date:</b> {{ $job->job_date?->format('d M Y') ?? '-' }}</div>
                <div class="col-md-3"><b>Due Date:</b> {{ $job->due_date?->format('d M Y') ?? '-' }}</div>
                <div class="col-md-6"><b>Notes:</b> {{ $job->notes ?? '-' }}</div>
            </div>
        </div>
    </div>

    {{-- Create Batch --}}
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Create New Batch</h5>
        </div>
        <div class="card-body">
            <div id="batchMessage"></div>

            <form id="createBatchForm">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Batch Date</label>
                        <input type="date" name="batch_date" class="form-control" value="{{ now()->toDateString() }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ $job->due_date?->toDateString() }}">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Optional">
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">+ Create Batch</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Batches --}}
    <div id="batchesArea">
        @forelse($job->batches as $batch)
            @include('tailoring.jobs.partials.batch_card', ['job' => $job, 'batch' => $batch])
        @empty
            <div class="card">
                <div class="card-body text-center text-muted">No batches yet. Create first batch.</div>
            </div>
        @endforelse
    </div>

    <script>
        // Create Batch
        document.getElementById('createBatchForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch("{{ route('tailoring.jobs.batches.store', $job) }}", {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            }).then(async res => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    document.getElementById('batchMessage').innerHTML =
                        `<div class="alert alert-danger">Error creating batch</div>`;
                    return;
                }

                document.getElementById('batchMessage').innerHTML =
                    `<div class="alert alert-success">${data.message}</div>`;
                setTimeout(() => document.getElementById('batchMessage').innerHTML = "", 2000);

                // Reload page (simple for now)
                setTimeout(() => window.location.reload(), 600);
            });
        });
    </script>
@endsection