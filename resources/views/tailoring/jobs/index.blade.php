@extends('layouts.vertical', ['subtitle' => 'Tailoring Jobs'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Tailoring Jobs', 'subtitle' => 'View'])

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title mb-0">Jobs</h5>
                <p class="card-subtitle">All tailoring jobs (Job → Batches → Items).</p>
            </div>
            <a href="{{ route('tailoring.jobs.create') }}" class="btn btn-primary">+ Create Job</a>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('tailoring.jobs.index') }}" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                        placeholder="Search by Job No / Customer name / phone">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-outline-secondary w-100" href="{{ route('tailoring.jobs.index') }}">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered">
                    <thead class="table-light">
                        <tr>
                            <th>Job No</th>
                            <th>Customer</th>
                            <th>Job Date</th>
                            <th>Due Date</th>
                            <th>Current Stage</th>
                            <th style="width: 160px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobs as $j)
                            <tr>
                                <td>{{ $j->job_no }}</td>
                                <td>{{ $j->customer?->full_name }}</td>
                                <td>{{ $j->job_date?->format('d M Y') ?? '-' }}</td>
                                <td>{{ $j->due_date?->format('d M Y') ?? '-' }}</td>
                                <td>{{ $j->currentStage?->name ?? '-' }}</td>
                                <td>
                                    <a class="btn btn-info btn-sm w-100" href="{{ route('tailoring.jobs.show', $j) }}">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No jobs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    {{ $jobs->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection