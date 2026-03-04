@extends('layouts.vertical', ['subtitle' => 'Tailoring Reports - Stages'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Tailoring Reports', 'subtitle' => 'Stage Dashboard'])

<style>
    .muted-help { font-size: 12px; color: #6c757d; }
    .report-card { border:1px solid rgba(0,0,0,.08); border-radius:14px; }
    .big { font-size: 22px; font-weight: 800; }
    .chip { background: rgba(13,110,253,.08); border-radius:999px; padding:4px 10px; font-size:12px; }
</style>

<div class="card report-card mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            <div class="col-md-4">
                <label class="form-label mb-1">Search (optional)</label>
                <input type="text" class="form-control" name="q" value="{{ $q }}" placeholder="Job No / Customer / Phone">
            </div>

            <div class="col-md-2">
                <label class="form-label mb-1">From</label>
                <input type="date" class="form-control" name="from" value="{{ $from }}">
            </div>

            <div class="col-md-2">
                <label class="form-label mb-1">To</label>
                <input type="date" class="form-control" name="to" value="{{ $to }}">
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100">Filter</button>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <a href="{{ route('tailoring.reports.stages') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>

        <div class="mt-3 muted-help">
            Shows items currently in each stage (not completed).
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card report-card"><div class="card-body">
            <div class="muted-help">Total Items (Live)</div>
            <div class="big">{{ (int)($totals->items_count ?? 0) }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card report-card"><div class="card-body">
            <div class="muted-help">Total Qty</div>
            <div class="big">{{ (int)($totals->qty_sum ?? 0) }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card report-card"><div class="card-body">
            <div class="muted-help">Total Amount</div>
            <div class="big">{{ number_format((float)($totals->amount_sum ?? 0), 2) }}</div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    @foreach($stages as $s)
        @php
            $stat = $stageSummary[$s->id] ?? ['items_count'=>0,'qty_sum'=>0,'amount_sum'=>0];
        @endphp
        <div class="col-md-4 col-xl-3">
            <div class="card report-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold">{{ $s->name }}</div>
                            <div class="muted-help">Stage {{ $s->sort_order }}</div>
                        </div>
                        <span class="chip">Live</span>
                    </div>

                    <div class="mt-2">
                        <div class="muted-help">Items</div>
                        <div class="big">{{ (int)$stat['items_count'] }}</div>
                    </div>

                    <div class="d-flex justify-content-between mt-2">
                        <div>
                            <div class="muted-help">Qty</div>
                            <div class="fw-semibold">{{ (int)$stat['qty_sum'] }}</div>
                        </div>
                        <div class="text-end">
                            <div class="muted-help">Amount</div>
                            <div class="fw-semibold">{{ number_format((float)$stat['amount_sum'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection