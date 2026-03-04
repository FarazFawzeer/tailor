@extends('layouts.vertical', ['subtitle' => 'Tailoring Reports - Staff'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Tailoring Reports', 'subtitle' => 'Staff Performance'])

<style>
    .muted-help { font-size: 12px; color: #6c757d; }
    .report-card { border:1px solid rgba(0,0,0,.08); border-radius:14px; }
    .pill { padding: 2px 10px; border-radius: 999px; font-size: 12px; background: rgba(13,110,253,.08); }
    .kpi { font-weight: 700; font-size: 15px; }
    .table td, .table th { vertical-align: middle; }
</style>

{{-- ✅ FILTER BAR (RIGHT ALIGNED like Jobs) --}}
<div class="card report-card mb-3">
    <div class="card-body">

        <div class="d-flex justify-content-end">
            <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">

                {{-- Search --}}
                <div class="input-group" style="width:320px;">
                    <span class="input-group-text bg-white">
                        <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                    </span>
                    <input type="text"
                        class="form-control"
                        name="q"
                        value="{{ $q }}"
                        placeholder="Search Jobs / Customer / Phone">
                </div>

                {{-- From --}}
                <input type="date"
                    class="form-control"
                    name="from"
                    value="{{ $from }}"
                    style="width:160px;"
                    title="From Date">

                {{-- To --}}
                <input type="date"
                    class="form-control"
                    name="to"
                    value="{{ $to }}"
                    style="width:160px;"
                    title="To Date">

                {{-- Staff --}}
                <select class="form-select"
                        name="staff_id"
                        style="width:180px;"
                        title="Staff">
                    <option value="">All Staff</option>
                    @foreach($staffList as $s)
                        <option value="{{ $s->id }}" {{ (string)$staffId === (string)$s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>

                <button class="btn btn-dark">
                    Search
                </button>

                <a class="btn btn-outline-secondary"
                   href="{{ route('tailoring.reports.staff') }}">
                    Reset
                </a>

            </form>
        </div>

        {{-- Info line --}}
        <div class="alert alert-light border mt-3 mb-0">
            <div class="muted-help mb-0">
                <b>Note:</b> Based on handover logs. Shows <b>Qty moved into each stage</b> by staff.
                Completion logs are not counted.
            </div>
        </div>

    </div>
</div>

{{-- ✅ REPORT TABLE --}}
<div class="card report-card">
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="min-width:220px;">Staff</th>
                        @foreach($stages as $st)
                            <th style="min-width:200px;">
                                {{ $st->name }}
                                <div class="muted-help">Qty • Moves • Unique Items</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @forelse($matrix as $uid => $info)
                        <tr>
                            {{-- Staff --}}
                            <td>
                                <div class="fw-semibold">{{ $info['name'] }}</div>
                                <div class="muted-help">ID: {{ $uid }}</div>
                            </td>

                            {{-- Each stage --}}
                            @foreach($stages as $st)
                                @php
                                    $cell = $info['stages'][$st->id] ?? ['qty'=>0,'moves'=>0,'uniqueItems'=>0];
                                @endphp
                                <td>
                                    <div class="kpi">{{ (int)$cell['qty'] }} Qty</div>
                                    <div class="d-flex flex-wrap gap-2 mt-1">
                                        <span class="badge bg-light text-dark border">
                                            {{ (int)$cell['moves'] }} Moves
                                        </span>
                                        <span class="badge bg-light text-dark border">
                                            {{ (int)$cell['uniqueItems'] }} Items
                                        </span>
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 1 + $stages->count() }}" class="text-center text-muted py-4">
                                No staff handover activity found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </div>
</div>
@endsection