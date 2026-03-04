@extends('layouts.vertical', ['subtitle' => 'Hiring Sales Report'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Hiring Reports', 'subtitle' => 'Sales & Pending'])

@php
    $statusMap = [
        '' => ['label'=>'All', 'cls'=>'bg-light text-dark border'],
        'issued' => ['label'=>'Issued', 'cls'=>'bg-warning text-dark'],
        'returned' => ['label'=>'Returned', 'cls'=>'bg-success'],
        'cancelled' => ['label'=>'Cancelled', 'cls'=>'bg-secondary'],
    ];
    $badge = $statusMap[$status ?? ''] ?? ['label'=>ucfirst($status), 'cls'=>'bg-secondary'];
@endphp

<style>
    .report-card { border:1px solid rgba(0,0,0,.08); border-radius:14px; }
    .muted-help { font-size:12px; color:#6c757d; }
    .kpi { border:1px solid rgba(0,0,0,.08); border-radius:14px; padding:12px; background:#fff; }
    .kpi .label { font-size:12px; color:#6c757d; }
    .kpi .val { font-weight:800; font-size:16px; }
    .table thead th { font-size:12px; text-transform:uppercase; letter-spacing:.03em; color:#6c757d; }
</style>

<div class="card report-card">
    <div class="card-header d-flex align-items-start justify-content-between">
        <div>
            <h5 class="card-title mb-0">Hiring Sales Report</h5>
            <div class="muted-help mt-1">
                Date Range: <b>{{ $from }}</b> to <b>{{ $to }}</b>
                <span class="text-muted">|</span>
                Status: <span class="badge {{ $badge['cls'] }}">{{ $badge['label'] }}</span>
            </div>
        </div>
    </div>

    <div class="card-body">

        {{-- Filters --}}
        <div class="border-0  mb-3">
            <div class="card-body py-3">
                <form method="GET">
                    <div class="row g-2 align-items-end justify-content-end">
                        <div class="col-md-2">
                            <label class="form-label mb-1">From</label>
                            <input type="date" name="from" class="form-control" value="{{ $from }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">To</label>
                            <input type="date" name="to" class="form-control" value="{{ $to }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                @foreach(['issued','returned','cancelled'] as $st)
                                    <option value="{{ $st }}" {{ ($status ?? '')===$st?'selected':'' }}>
                                        {{ ucfirst($st) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="ti ti-search me-1"></i> Apply
                            </button>
                            <a href="{{ route('hiring.reports.sales') }}" class="btn btn-light border w-100">
                                <i class="ti ti-refresh me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <div class="kpi">
                    <div class="label">Agreements</div>
                    <div class="val">{{ (int)($summary->agreements ?? 0) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi">
                    <div class="label">Hire Total</div>
                    <div class="val">Rs {{ number_format($hireTotal,2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi">
                    <div class="label">Fine Total</div>
                    <div class="val">Rs {{ number_format($fineTotal,2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi">
                    <div class="label">Grand Total</div>
                    <div class="val">Rs {{ number_format($grandTotal,2) }}</div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-4">
            <div class="col-md-4">
                <div class="kpi">
                    <div class="label">Deposit Collected</div>
                    <div class="val">Rs {{ number_format($depositTotal,2) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kpi">
                    <div class="label">Other Payments (Paid)</div>
                    <div class="val">Rs {{ number_format($paidTotal,2) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kpi">
                    <div class="label">Pending (Outstanding)</div>
                    <div class="val text-danger">Rs {{ number_format($pending,2) }}</div>
                    <div class="muted-help">Grand Total - (Deposit + Paid)</div>
                </div>
            </div>
        </div>

        {{-- Daily Breakdown --}}
        <div class="card border mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <b>Daily Summary</b>
                <div class="muted-help">Shows totals per issue date</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Agreements</th>
                                <th class="text-end">Hire</th>
                                <th class="text-end">Fine</th>
                                <th class="text-end">Collected</th>
                                <th class="text-end">Pending</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($daily as $d)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($d['date'])->format('d M Y') }}</td>
                                    <td class="text-end fw-semibold">{{ $d['agreements'] }}</td>
                                    <td class="text-end">Rs {{ number_format($d['hire_total'],2) }}</td>
                                    <td class="text-end">Rs {{ number_format($d['fine_total'],2) }}</td>
                                    <td class="text-end fw-semibold">Rs {{ number_format($d['collected'],2) }}</td>
                                    <td class="text-end fw-semibold {{ $d['pending']>0?'text-danger':'' }}">
                                        Rs {{ number_format($d['pending'],2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Agreement list --}}
        <div class="card border">
            <div class="card-header"><b>Agreement Details</b></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Customer</th>
                                <th>Issue</th>
                                <th>Status</th>
                                <th class="text-end">Grand</th>
                                <th class="text-end">Collected</th>
                                <th class="text-end">Pending</th>
                                <th style="width:120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($agreements as $a)
                                @php
                                    $grand = (float)($a->total_hire_amount ?? 0) + (float)($a->fine_amount ?? 0);
                                    $col   = (float)($a->deposit_received ?? 0) + (float)($a->amount_paid ?? 0);
                                    $pend  = max(0, $grand - $col);

                                    $cls = match($a->status){
                                        'issued'=>'bg-warning text-dark',
                                        'returned'=>'bg-success',
                                        'cancelled'=>'bg-secondary',
                                        default=>'bg-secondary'
                                    };
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $a->agreement_no }}</td>
                                    <td>{{ $a->customer?->full_name ?? 'N/A' }}</td>
                                    <td>{{ optional($a->issue_date)->format('d M Y') }}</td>
                                    <td><span class="badge {{ $cls }}">{{ ucfirst($a->status) }}</span></td>
                                    <td class="text-end fw-semibold">Rs {{ number_format($grand,2) }}</td>
                                    <td class="text-end">Rs {{ number_format($col,2) }}</td>
                                    <td class="text-end fw-semibold {{ $pend>0?'text-danger':'' }}">Rs {{ number_format($pend,2) }}</td>
                                    <td>
                                        <a class="btn btn-outline-primary btn-sm w-100"
                                           href="{{ route('hiring.agreements.show', $a->id) }}">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">No agreements found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $agreements->links() }}
        </div>

    </div>
</div>
@endsection