@extends('layouts.vertical', ['subtitle' => 'Hire Agreements'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Hiring', 'subtitle' => 'Agreements'])

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Hire Agreements</h5>
            <p class="card-subtitle mb-0">Issue / Return agreements and track fine.</p>
        </div>

        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input name="q" class="form-control" value="{{ $q ?? '' }}" placeholder="Search agreement no / customer name">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        @foreach(['issued','returned','cancelled'] as $st)
                            <option value="{{ $st }}" {{ ($status ?? '') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('hiring.agreements.create') }}" class="btn btn-primary w-100">Create</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Agreement</th>
                            <th>Customer</th>
                            <th>Issue</th>
                            <th>Expected Return</th>
                            <th>Status</th>
                            <th>Hire Total</th>
                            <th>Fine</th>
                            <th width="160">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agreements as $a)
                            <tr>
                                <td><b>{{ $a->agreement_no }}</b></td>
                                <td>{{ $a->customer?->full_name ?? 'N/A' }}</td>
                                <td>{{ $a->issue_date?->format('d M Y') }}</td>
                                <td>{{ $a->expected_return_date?->format('d M Y') }}</td>
                                <td>
                                    @if($a->status === 'issued')
                                        <span class="badge bg-warning">Issued</span>
                                    @elseif($a->status === 'returned')
                                        <span class="badge bg-success">Returned</span>
                                    @else
                                        <span class="badge bg-secondary">Cancelled</span>
                                    @endif
                                </td>
                                <td>{{ number_format((float)$a->total_hire_amount, 2) }}</td>
                                <td>{{ number_format((float)$a->fine_amount, 2) }}</td>
                                <td>
                                    <a href="{{ route('hiring.agreements.show', $a) }}" class="btn btn-outline-dark btn-sm w-100">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">No agreements found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $agreements->links() }}
            </div>
        </div>
    </div>
@endsection