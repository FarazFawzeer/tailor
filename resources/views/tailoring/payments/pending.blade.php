@extends('layouts.vertical', ['subtitle' => 'Pending Bills'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Tailoring', 'subtitle' => 'Pending Bills'])

    {{-- Summary --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="text-muted small">Total Outstanding</div>
                    <div class="fs-4 fw-bold text-danger">{{ number_format($summary['outstanding'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('tailoring.payments.pending', array_filter(['q' => $q])) }}" class="card mb-0 h-100 text-reset {{ $filter === '' ? 'border-primary' : '' }}">
                <div class="card-body">
                    <div class="text-muted small">Pending Bills</div>
                    <div class="fs-4 fw-bold">{{ $summary['count'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('tailoring.payments.pending', array_filter(['q' => $q, 'filter' => 'ready'])) }}" class="card mb-0 h-100 text-reset {{ $filter === 'ready' ? 'border-primary' : '' }}">
                <div class="card-body">
                    <div class="text-muted small">Ready to Deliver (collect now)</div>
                    <div class="fs-4 fw-bold text-success">{{ $summary['ready'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('tailoring.payments.pending', array_filter(['q' => $q, 'filter' => 'overdue'])) }}" class="card mb-0 h-100 text-reset {{ $filter === 'overdue' ? 'border-primary' : '' }}">
                <div class="card-body">
                    <div class="text-muted small">Past Due Date</div>
                    <div class="fs-4 fw-bold text-warning">{{ $summary['overdue'] }}</div>
                </div>
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Pending Bills</h5>
            <p class="card-subtitle mb-0">Jobs where the customer still has a balance to pay. Nearest due date first.</p>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('tailoring.payments.pending') }}" class="row g-2 mb-3">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" value="{{ $q }}"
                        placeholder="Search by Job No / Customer / Phone">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100" type="submit">Search</button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-outline-secondary w-100" href="{{ route('tailoring.payments.pending') }}">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Job No</th>
                            <th>Customer</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th class="text-end">Bill Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jobs as $j)
                            @php
                                $isReady = (int)$j->not_ready_items === 0;
                                $isOverdue = $j->due_date && $j->due_date->lt(today());
                                $advance = $j->payments->where('payment_type', 'advance')->sum('amount');
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $j->job_no }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $j->customer?->full_name ?? '-' }}</div>
                                    <div class="text-muted small">{{ $j->customer?->phone ?? '' }}</div>
                                </td>
                                <td>
                                    {{ $j->due_date?->format('d M Y') ?? '-' }}
                                    @if ($isOverdue)
                                        <div><span class="badge bg-warning text-dark">Past due</span></div>
                                    @endif
                                </td>
                                <td>
                                    @if ($isReady)
                                        <span class="badge bg-success">Ready to deliver</span>
                                    @else
                                        <span class="badge bg-info">In production</span>
                                    @endif
                                    @if ((float)$j->bill_paid_total <= 0)
                                        <div><span class="badge bg-danger mt-1">No payment yet</span></div>
                                    @elseif ($advance > 0)
                                        <div class="text-muted small mt-1">Advance: {{ number_format((float)$advance, 2) }}</div>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format((float)$j->bill_grand_total, 2) }}</td>
                                <td class="text-end text-success">{{ number_format((float)$j->bill_paid_total, 2) }}</td>
                                <td class="text-end fw-bold text-danger">{{ number_format((float)$j->bill_balance, 2) }}</td>
                                <td>
                                    <a href="{{ route('tailoring.jobs.show', $j) }}#paymentsCard"
                                        class="btn btn-sm w-100 {{ auth()->user()->can('payments.create') ? 'btn-success' : 'btn-outline-primary' }}">
                                        {{ auth()->user()->can('payments.create') ? 'Collect' : 'View' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No pending bills.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
@endsection
