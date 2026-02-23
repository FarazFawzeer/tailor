@extends('layouts.vertical', ['subtitle' => 'Availability Dashboard'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Hiring', 'subtitle' => 'Availability Dashboard'])

    {{-- Summary Cards --}}
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Items</p>
                    <h3 class="mb-0">{{ $totalItems }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Available Now</p>
                    <h3 class="mb-0 text-success">{{ $availableCount }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Hired Out</p>
                    <h3 class="mb-0 text-primary">{{ $hiredCount }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Overdue Agreements</p>
                    <h3 class="mb-0 text-danger">{{ $overdueCount }}</h3>
                    <div class="mt-2">
                        <a class="btn btn-outline-danger btn-sm" href="{{ route('hiring.availability.overdue') }}">View All</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Optional status cards --}}
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Maintenance</p>
                    <h4 class="mb-0">{{ $maintenanceCount }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Damaged</p>
                    <h4 class="mb-0">{{ $damagedCount }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Availability by category --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Availability by Category</h5>
            <p class="card-subtitle mb-0">Shows stock split (Available vs Hired) and utilization.</p>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th>Total</th>
                            <th>Available</th>
                            <th>Hired</th>
                            <th>Utilization %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categoryStats as $row)
                            <tr>
                                <td>{{ $row->category }}</td>
                                <td><b>{{ $row->total }}</b></td>
                                <td><span class="badge bg-success">{{ $row->available }}</span></td>
                                <td><span class="badge bg-primary">{{ $row->hired }}</span></td>
                                <td>{{ number_format((float)$row->utilization, 1) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Upcoming returns --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Upcoming Returns (Next 7 days)</h5>
                <p class="card-subtitle mb-0">Agreements that should return soon.</p>
            </div>
            <a class="btn btn-outline-dark btn-sm" href="{{ route('hiring.availability.upcoming') }}">View All</a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Agreement</th>
                            <th>Customer</th>
                            <th>Expected Return</th>
                            <th>Items Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingReturns as $r)
                            <tr>
                                <td><b>{{ $r->agreement_no }}</b></td>
                                <td>{{ $r->full_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($r->expected_return_date)->format('d M Y') }}</td>
                                <td><span class="badge bg-primary">{{ $r->items_out }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No upcoming returns.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Overdue small preview --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Overdue (Top 10)</h5>
            <p class="card-subtitle mb-0">These agreements are past expected return date.</p>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Agreement</th>
                            <th>Issue</th>
                            <th>Expected Return</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overdueAgreements as $o)
                            <tr>
                                <td><b>{{ $o->agreement_no }}</b></td>
                                <td>{{ \Carbon\Carbon::parse($o->issue_date)->format('d M Y') }}</td>
                                <td><span class="badge bg-danger">
                                    {{ \Carbon\Carbon::parse($o->expected_return_date)->format('d M Y') }}
                                </span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No overdue agreements.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection