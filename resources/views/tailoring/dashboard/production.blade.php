@extends('layouts.vertical', ['subtitle' => 'Production Dashboard'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Tailoring', 'subtitle' => 'Production Dashboard'])

    <style>
        .stat-card {
            border: 1px solid rgba(0, 0, 0, .07);
            border-radius: 14px;
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .muted {
            font-size: 12px;
            color: #6c757d;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }
    </style>

    <style>
        .stage-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .stage-card {
            flex: 0 0 calc(20% - 12px);
            /* 5 cards per row */
        }

        @media (max-width:1200px) {
            .stage-card {
                flex: 0 0 calc(33.33% - 12px);
            }

            /* tablet */
        }

        @media (max-width:768px) {
            .stage-card {
                flex: 0 0 calc(50% - 12px);
            }

            /* mobile */
        }

        @media (max-width:500px) {
            .stage-card {
                flex: 0 0 100%;
            }
        }
    </style>


    <div class="stage-row">

        @foreach ($stages as $s)
            @php
                $count = (int) ($stageCounts[$s->id] ?? 0);
                $qty = (int) ($stageQtyCounts[$s->id] ?? 0);
                $isLast = $lastStage && $lastStage->id === $s->id;
            @endphp

            <div class="stage-card">
                <div class="card stat-card h-100">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>
                                <div class="text-muted">{{ $s->name }}</div>

                                <div class="d-flex align-items-end gap-2">
                                    <h2 class="mb-0">{{ $count }}</h2>
                                    <span class="muted mb-1">items</span>
                                </div>

                                <div class="muted">Total Qty: <b>{{ $qty }}</b></div>

                                @if ($isLast)
                                    <div class="mt-2">
                                        <span class="badge bg-success">Ready for Delivery</span>
                                    </div>
                                @endif
                            </div>

                            <div class="stat-icon bg-primary bg-opacity-10">
                                <iconify-icon icon="{{ $isLast ? 'solar:box-outline' : 'solar:scissors-outline' }}"
                                    class="fs-28 text-primary">
                                </iconify-icon>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        @endforeach

    </div>


    <div class="row mt-3">

        {{-- OVERDUE ITEMS --}}
        @if ($hasDueDate && $overdueItems->count())
            <div class="col-lg-4 mb-3">
                <div class="card stat-card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-danger">Overdue </h5>
                        <span class="muted">Most urgent</span>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Job</th>
                                        <th>Customer</th>


                                        <th>Due</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($overdueItems->take(5) as $o)
                                        <tr>
                                            <td>{{ $o->job_no }}</td>
                                            <td>{{ $o->full_name ?? 'N/A' }}</td>


                                            <td class="text-danger">
                                                {{ \Carbon\Carbon::parse($o->due_date)->format('d M Y') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif


        {{-- LATEST ITEMS --}}
        <div class="col-lg-8 mb-3">
            <div class="card stat-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Latest Items</h5>
                        <div class="muted">Last updated </div>
                    </div>

                    <a href="{{ route('tailoring.jobs.index') }}" class="btn btn-outline-primary btn-sm">
                        View Jobs
                    </a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">

                            <thead class="table-light">
                                <tr>
                                    <th>Job</th>
                                    <th>Customer</th>
                                    <th>Dress</th>
                                    <th class="text-end">Qty</th>
                                    <th>Stage</th>
                                    <th>Due</th>
                                    <th>Updated</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($latestItems->take(10) as $it)
                                    <tr>
                                        <td>{{ $it->job_no }}</td>
                                        <td>{{ $it->full_name ?? 'N/A' }}</td>
                                        <td>{{ $it->dress_name ?? 'N/A' }}</td>
                                        <td class="text-end">{{ $it->qty }}</td>

                                        <td>
                                            <span class="badge bg-info" style="width: 75px;">
                                                {{ $it->stage_name ?? 'N/A' }}
                                            </span>
                                        </td>

                                        <td>
                                            @if (!empty($it->due_date))
                                                {{ \Carbon\Carbon::parse($it->due_date)->format('d M Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($it->updated_at)->format('d M Y, h:i A') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            No production items found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- SUMMARY --}}
    <div class="row g-3 mt-1">
        <div class="col-12 col-lg-6">
            <div class="card stat-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Quick Summary</h5>
                    <span class="muted">Today</span>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Ready Items</span>
                        <b>{{ $readyForDeliveryCount }}</b>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Ready Qty</span>
                        <b>{{ $readyForDeliveryQty }}</b>
                    </div>

                    @if ($hasDueDate)
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-danger">Overdue Items</span>
                            <b class="text-danger">{{ $overdueCount }}</b>
                        </div>
                        <div class="muted">Based on job due date</div>
                    @else
                        <div class="alert alert-info mb-0">
                            Overdue tracking disabled (jobs.due_date column not found).
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- SIMPLE NOTE CARD --}}
        <div class="col-12 col-lg-6">
            <div class="card stat-card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">Use <b>Measurements</b> only when template is selected.</li>
                        <li class="mb-2">Make sure <b>Job Due Date</b> and <b>Batch Due Date</b> are filled.</li>
                        <li class="mb-0">If item is delayed, check <b>Overdue Items</b> count.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endsection
