@extends('layouts.vertical', ['subtitle' => 'Dashboard'])

@section('content')
    @php
        $hour = (int)now()->format('H');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $canJobs = auth()->user()->can('jobs.view');

        // summary cards (money cards only for users who may see payments)
        $cards = [
            ['label' => 'Active Jobs', 'value' => number_format($kpi['active_jobs']), 'sub' => 'Jobs with pieces still open',
                'icon' => 'solar:clipboard-list-bold-duotone', 'tone' => 'primary', 'href' => $canJobs ? route('tailoring.jobs.index') : null],
            ['label' => 'In Production', 'value' => number_format($kpi['pieces_in_production']), 'sub' => 'Pieces being made',
                'icon' => 'solar:scissors-bold-duotone', 'tone' => 'info', 'href' => null],
            ['label' => 'Ready to Deliver', 'value' => number_format($kpi['ready_pieces']), 'sub' => $kpi['ready_jobs'] . ' job(s) waiting for customer',
                'icon' => 'solar:box-bold-duotone', 'tone' => 'success', 'href' => null],
            ['label' => 'Overdue Jobs', 'value' => number_format($kpi['overdue_jobs']), 'sub' => $kpi['due_week'] . ' more due this week',
                'icon' => 'solar:alarm-bold-duotone', 'tone' => 'danger', 'href' => null],
        ];
        if ($money) {
            $cards[] = ['label' => 'Outstanding', 'value' => number_format($money['outstanding'], 2), 'sub' => $money['pending_count'] . ' pending bill(s)',
                'icon' => 'solar:wallet-money-bold-duotone', 'tone' => 'warning', 'href' => route('tailoring.payments.pending')];
            $cards[] = ['label' => 'Collected (Month)', 'value' => number_format($money['collected_month'], 2), 'sub' => 'Today: ' . number_format($money['collected_today'], 2),
                'icon' => 'solar:hand-money-bold-duotone', 'tone' => 'success', 'href' => null];
        } else {
            $cards[] = ['label' => 'Due This Week', 'value' => number_format($kpi['due_week']), 'sub' => 'Next 7 days',
                'icon' => 'solar:calendar-bold-duotone', 'tone' => 'warning', 'href' => null];
            $cards[] = ['label' => 'New Jobs (Month)', 'value' => number_format($kpi['new_jobs_month']), 'sub' => now()->format('F Y'),
                'icon' => 'solar:add-square-bold-duotone', 'tone' => 'primary', 'href' => null];
        }

        $maxStagePieces = max(1, (int)$stageChart->max('pieces'));
        $maxStaffPieces = max(1, (int)$staffWorkload->max('pieces'));
    @endphp

    <style>
        .dash-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin: 4px 0 20px; }
        .dash-head h4 { margin: 0; font-weight: 700; }
        .dash-card { border: 1px solid var(--taplox-border-color, rgba(0,0,0,.08)); border-radius: 14px; box-shadow: none; height: 100%; margin-bottom: 0; }
        .dash-card > .card-header { background: transparent; border-bottom: 1px solid var(--taplox-border-color, rgba(0,0,0,.06)); padding: 14px 18px; }
        .dash-card > .card-header .card-title { font-size: 15px; font-weight: 700; margin: 0; }
        .dash-card > .card-header .sub { font-size: 12px; color: var(--taplox-secondary-color, #8a94a6); }
        .kpi { padding: 14px 16px; display: flex; gap: 12px; align-items: center; text-decoration: none; color: inherit; height: 100%; }
        .kpi-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 26px; }
        /* always 6 cards: 2x3 on phones, 3x2 on tablets/laptops, 6x1 on wide screens */
        .kpi-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        @media (min-width: 768px) { .kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (min-width: 1500px) { .kpi-grid { grid-template-columns: repeat(6, minmax(0, 1fr)); } }
        @media (max-width: 575.98px) { .kpi { padding: 12px; gap: 10px; } .kpi-icon { width: 38px; height: 38px; font-size: 20px; } .kpi-value { font-size: 17px; } }
        .kpi-label { font-size: 12.5px; color: var(--taplox-secondary-color, #8a94a6); margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .kpi-value { font-size: 21px; font-weight: 700; line-height: 1.15; white-space: nowrap; }
        .kpi-sub { font-size: 11.5px; color: var(--taplox-secondary-color, #8a94a6); margin-top: 2px; }
        a.kpi:hover .kpi-value { color: var(--taplox-primary, #1a80f8); }

        .pipeline { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
        @media (min-width: 992px) { .pipeline { grid-template-columns: repeat(var(--stages, 6), minmax(0, 1fr)); } }
        @media (max-width: 400px) { .pipeline { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .pipe-step { border: 1px solid var(--taplox-border-color, rgba(0,0,0,.08)); border-radius: 12px; padding: 10px 12px; }
        .pipe-step.is-delivery { border-color: rgba(var(--taplox-success-rgb, 23,197,83), .45); background: rgba(var(--taplox-success-rgb, 23,197,83), .06); }
        .pipe-name { font-size: 12px; color: var(--taplox-secondary-color, #8a94a6); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pipe-val { font-size: 20px; font-weight: 700; line-height: 1.2; }
        .pipe-meta { font-size: 11px; color: var(--taplox-secondary-color, #8a94a6); }
        .pipe-bar { height: 4px; border-radius: 4px; background: rgba(128,128,128,.15); margin-top: 8px; overflow: hidden; }
        .pipe-bar > span { display: block; height: 100%; border-radius: 4px; background: var(--taplox-primary, #1a80f8); }
        .pipe-step.is-delivery .pipe-bar > span { background: var(--taplox-success, #17c553); }

        .dash-table { margin: 0; }
        .dash-table th { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; color: var(--taplox-secondary-color, #8a94a6); white-space: nowrap; }
        .dash-table td, .dash-table th { padding: 10px 16px; vertical-align: middle; }
        .dash-table tbody tr:last-child td { border-bottom: 0; }
        .empty-state { padding: 28px 12px; text-align: center; color: var(--taplox-secondary-color, #8a94a6); font-size: 13px; }
        .empty-state iconify-icon { font-size: 34px; display: block; margin: 0 auto 6px; opacity: .6; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 6px; }
        .staff-row + .staff-row { margin-top: 14px; }
        .progress-thin { height: 6px; border-radius: 6px; }
        .min-w-0 { min-width: 0; }
    </style>

    {{-- ===== Header ===== --}}
    <div class="dash-head">
        <div>
            <h4>{{ $greeting }}, {{ auth()->user()->name }}</h4>
            <div class="text-muted small">{{ now()->format('l, d F Y') }} &middot; Here's what is happening in the shop today.</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @can('jobs.create')
                <a href="{{ route('tailoring.jobs.createWizard') }}" class="btn btn-primary">
                    <iconify-icon icon="solar:add-circle-outline" class="align-middle me-1"></iconify-icon> New Job
                </a>
            @endcan
            @can('customers.create')
                <a href="{{ route('customers.create') }}" class="btn btn-outline-primary">
                    <iconify-icon icon="solar:user-plus-outline" class="align-middle me-1"></iconify-icon> New Customer
                </a>
            @endcan
            @can('hire_agreements.create')
                <a href="{{ route('hiring.agreements.create') }}" class="btn btn-outline-secondary">
                    <iconify-icon icon="solar:document-add-outline" class="align-middle me-1"></iconify-icon> New Agreement
                </a>
            @endcan
        </div>
    </div>

    {{-- ===== KPI cards ===== --}}
    <div class="kpi-grid mb-3">
        @foreach ($cards as $c)
            <div>
                <div class="card dash-card">
                    @if ($c['href'])
                        <a href="{{ $c['href'] }}" class="kpi">
                    @else
                        <div class="kpi">
                    @endif
                        <div class="kpi-icon bg-{{ $c['tone'] }}-subtle text-{{ $c['tone'] }}">
                            <iconify-icon icon="{{ $c['icon'] }}"></iconify-icon>
                        </div>
                        <div class="min-w-0">
                            <div class="kpi-label">{{ $c['label'] }}</div>
                            <div class="kpi-value">{{ $c['value'] }}</div>
                            <div class="kpi-sub text-truncate">{{ $c['sub'] }}</div>
                        </div>
                    @if ($c['href'])
                        </a>
                    @else
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- ===== Production pipeline + payment status ===== --}}
    <div class="row g-3 mb-3">
        <div class="{{ $money ? 'col-xl-8' : 'col-12' }}">
            <div class="card dash-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Production Pipeline</h5>
                        <div class="sub">Open pieces in each stage</div>
                    </div>
                    @can('workqueue.all_stages')
                        <a href="{{ route('tailoring.workqueue.index') }}" class="btn btn-sm btn-light">Work Queue</a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="pipeline mb-3" style="--stages: {{ max(1, $stageChart->count()) }};">
                        @foreach ($stageChart as $s)
                            <div class="pipe-step {{ $s['is_delivery'] ? 'is-delivery' : '' }}">
                                <div class="pipe-name" title="{{ $s['name'] }}">{{ $loop->iteration }}. {{ $s['name'] }}</div>
                                <div class="pipe-val">{{ $s['pieces'] }}</div>
                                <div class="pipe-meta">{{ $s['items'] }} item(s){{ $s['is_delivery'] ? ' · ready' : '' }}</div>
                                <div class="pipe-bar"><span style="width: {{ round($s['pieces'] / $maxStagePieces * 100) }}%"></span></div>
                            </div>
                        @endforeach
                    </div>
                    <div id="stageChart" style="min-height: 260px;"></div>
                </div>
            </div>
        </div>

        @if ($money)
            <div class="col-xl-4">
                <div class="card dash-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Payment Status</h5>
                            <div class="sub">All jobs with a bill amount</div>
                        </div>
                        <a href="{{ route('tailoring.payments.pending') }}" class="btn btn-sm btn-light">Pending Bills</a>
                    </div>
                    <div class="card-body">
                        @if (array_sum($money['status']) > 0)
                            <div id="paymentChart" style="min-height: 250px;"></div>
                            <div class="d-flex justify-content-around text-center mt-2">
                                <div><span class="legend-dot bg-success"></span><span class="small">Paid</span><div class="fw-bold">{{ $money['status']['paid'] }}</div></div>
                                <div><span class="legend-dot bg-warning"></span><span class="small">Partly paid</span><div class="fw-bold">{{ $money['status']['partial'] }}</div></div>
                                <div><span class="legend-dot bg-danger"></span><span class="small">Unpaid</span><div class="fw-bold">{{ $money['status']['unpaid'] }}</div></div>
                            </div>
                        @else
                            <div class="empty-state"><iconify-icon icon="solar:wallet-outline"></iconify-icon>No bills yet.</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ===== Activity trend + staff workload ===== --}}
    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="card dash-card">
                <div class="card-header">
                    <h5 class="card-title">Last 14 Days</h5>
                    <div class="sub">New jobs{{ $money ? ' and money collected' : '' }} per day</div>
                </div>
                <div class="card-body">
                    <div id="trendChart" style="min-height: 280px;"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card dash-card">
                <div class="card-header">
                    <h5 class="card-title">Staff Workload</h5>
                    <div class="sub">Pieces received in the last 30 days</div>
                </div>
                <div class="card-body">
                    @forelse ($staffWorkload as $w)
                        <div class="staff-row">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold">{{ $w->staff_name }}</span>
                                <span class="small text-muted">{{ (int)$w->pieces }} pcs &middot; {{ (int)$w->handovers }} handovers</span>
                            </div>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-primary" style="width: {{ round($w->pieces / $maxStaffPieces * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state"><iconify-icon icon="solar:users-group-rounded-outline"></iconify-icon>No handovers in the last 30 days.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Overdue + due this week ===== --}}
    <div class="row g-3 mb-3">
        @foreach ([
            ['title' => 'Overdue Jobs', 'sub' => 'Past due date, pieces still in production', 'rows' => $overdueJobs, 'overdue' => true, 'empty' => 'Nothing overdue. Great work!', 'icon' => 'solar:check-circle-outline'],
            ['title' => 'Due This Week', 'sub' => 'Due in the next 7 days', 'rows' => $dueSoonJobs, 'overdue' => false, 'empty' => 'No jobs due in the next 7 days.', 'icon' => 'solar:calendar-outline'],
        ] as $box)
            <div class="col-xl-6">
                <div class="card dash-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title {{ $box['overdue'] ? 'text-danger' : '' }}">{{ $box['title'] }}</h5>
                            <div class="sub">{{ $box['sub'] }}</div>
                        </div>
                        @if ($box['overdue'] && $kpi['overdue_jobs'] > $box['rows']->count())
                            <span class="badge bg-danger-subtle text-danger">+{{ $kpi['overdue_jobs'] - $box['rows']->count() }} more</span>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        @if ($box['rows']->isEmpty())
                            <div class="empty-state"><iconify-icon icon="{{ $box['icon'] }}"></iconify-icon>{{ $box['empty'] }}</div>
                        @else
                            <div class="table-responsive">
                                <table class="table dash-table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Job</th>
                                            <th>Customer</th>
                                            <th>Due</th>
                                            <th class="text-end">Open pcs</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($box['rows'] as $j)
                                            @php $days = (int)$j->due_date->diffInDays(today(), false); @endphp
                                            <tr>
                                                <td class="fw-semibold">
                                                    @if ($canJobs)
                                                        <a href="{{ route('tailoring.jobs.show', $j) }}">{{ $j->job_no }}</a>
                                                    @else
                                                        {{ $j->job_no }}
                                                    @endif
                                                </td>
                                                <td>
                                                    <div>{{ $j->customer?->full_name ?? '-' }}</div>
                                                    <div class="small text-muted">{{ $j->customer?->phone }}</div>
                                                </td>
                                                <td class="text-nowrap">
                                                    {{ $j->due_date->format('d M Y') }}
                                                    <div class="small {{ $box['overdue'] ? 'text-danger' : 'text-muted' }}">
                                                        @if ($box['overdue'])
                                                            {{ $days }} day(s) late
                                                        @elseif ($days === 0)
                                                            Today
                                                        @else
                                                            in {{ abs($days) }} day(s)
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-end fw-semibold">{{ (int)$j->pending_pieces }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ===== Recent jobs ===== --}}
    <div class="card dash-card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title">Recent Jobs</h5>
                <div class="sub">Latest orders and how far they are</div>
            </div>
            @if ($canJobs)
                <a href="{{ route('tailoring.jobs.index') }}" class="btn btn-sm btn-light">View all jobs</a>
            @endif
        </div>
        <div class="card-body p-0">
            @if ($recentJobs->isEmpty())
                <div class="empty-state"><iconify-icon icon="solar:clipboard-list-outline"></iconify-icon>No jobs yet.</div>
            @else
                <div class="table-responsive">
                    <table class="table dash-table table-hover">
                        <thead>
                            <tr>
                                <th>Job</th>
                                <th>Customer</th>
                                <th>Job Date</th>
                                <th>Due</th>
                                <th style="min-width: 170px;">Progress</th>
                                @if ($money)
                                    <th class="text-end">Bill</th>
                                    <th>Payment</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentJobs as $j)
                                @php
                                    $total = (int)$j->total_pieces;
                                    $done = (int)$j->done_pieces;
                                    $pct = $total > 0 ? round($done / $total * 100) : 0;
                                @endphp
                                <tr>
                                    <td class="fw-semibold">
                                        @if ($canJobs)
                                            <a href="{{ route('tailoring.jobs.show', $j) }}">{{ $j->job_no }}</a>
                                        @else
                                            {{ $j->job_no }}
                                        @endif
                                    </td>
                                    <td>{{ $j->customer?->full_name ?? '-' }}</td>
                                    <td class="text-nowrap">{{ $j->job_date?->format('d M Y') ?? '-' }}</td>
                                    <td class="text-nowrap">{{ $j->due_date?->format('d M Y') ?? '-' }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress progress-thin flex-grow-1">
                                                <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="small text-muted text-nowrap">{{ $done }}/{{ $total }} ready</span>
                                        </div>
                                    </td>
                                    @if ($money)
                                        <td class="text-end text-nowrap">{{ number_format((float)$j->bill_grand_total, 2) }}</td>
                                        <td>
                                            @if ((float)$j->bill_grand_total <= 0)
                                                <span class="badge bg-light text-muted">No bill</span>
                                            @elseif ((float)$j->bill_balance <= 0.009)
                                                <span class="badge bg-success-subtle text-success">Paid</span>
                                            @elseif ((float)$j->bill_paid_total > 0)
                                                <span class="badge bg-warning-subtle text-warning">Due {{ number_format((float)$j->bill_balance, 2) }}</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Unpaid</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>
    <script>
        (function() {
            if (typeof ApexCharts === 'undefined') return; // offline: page still works without charts

            const css = getComputedStyle(document.documentElement);
            const color = (name, fallback) => (css.getPropertyValue(name) || '').trim() || fallback;
            const primary = color('--taplox-primary', '#1a80f8');
            const success = color('--taplox-success', '#17c553');
            const warning = color('--taplox-warning', '#f0934e');
            const danger = color('--taplox-danger', '#f42557');
            const muted = color('--taplox-secondary-color', '#8a94a6');
            const base = {
                chart: { fontFamily: 'inherit', toolbar: { show: false }, foreColor: muted },
                grid: { borderColor: 'rgba(128,128,128,.15)', strokeDashArray: 4 },
                dataLabels: { enabled: false },
            };

            // pieces per stage
            const stages = @json($stageChart);
            new ApexCharts(document.getElementById('stageChart'), {
                ...base,
                chart: { ...base.chart, type: 'bar', height: 260 },
                series: [{ name: 'Pieces', data: stages.map(s => s.pieces) }],
                xaxis: { categories: stages.map(s => s.name) },
                yaxis: { labels: { formatter: v => Math.round(v) } },
                colors: stages.map(s => s.is_delivery ? success : primary),
                plotOptions: { bar: { borderRadius: 6, columnWidth: '45%', distributed: true, dataLabels: { position: 'top' } } },
                legend: { show: false },
                dataLabels: { enabled: true, offsetY: -20, style: { colors: [muted], fontWeight: 600 } },
                tooltip: { y: { formatter: (v, o) => `${v} pcs · ${stages[o.dataPointIndex].items} item(s)` } },
            }).render();

            // payment status donut
            @if ($money && array_sum($money['status']) > 0)
                const status = @json($money['status']);
                new ApexCharts(document.getElementById('paymentChart'), {
                    ...base,
                    chart: { ...base.chart, type: 'donut', height: 250 },
                    series: [status.paid, status.partial, status.unpaid],
                    labels: ['Paid', 'Partly paid', 'Unpaid'],
                    colors: [success, warning, danger],
                    legend: { show: false },
                    stroke: { width: 0 },
                    plotOptions: {
                        pie: { donut: { size: '72%', labels: { show: true, total: { show: true, label: 'Jobs', color: muted } } } }
                    },
                }).render();
            @endif

            // last 14 days
            const trend = @json($trend);
            const withMoney = @json((bool)$money);
            const series = [{ name: 'New jobs', type: 'column', data: trend.jobs }];
            if (withMoney) series.push({ name: 'Collected', type: 'area', data: trend.collected });

            new ApexCharts(document.getElementById('trendChart'), {
                ...base,
                chart: { ...base.chart, type: 'line', height: 280 },
                series,
                colors: [primary, success],
                stroke: { width: withMoney ? [0, 2.5] : [0], curve: 'smooth' },
                fill: { type: withMoney ? ['solid', 'gradient'] : ['solid'], gradient: { opacityFrom: .35, opacityTo: .02 } },
                plotOptions: { bar: { borderRadius: 4, columnWidth: '40%' } },
                xaxis: { categories: trend.labels, labels: { rotate: 0, hideOverlappingLabels: true } },
                // fixed minimum scale so an empty period still draws a clean chart
                yaxis: withMoney ? [
                    { title: { text: 'Jobs' }, min: 0, max: Math.max(4, ...trend.jobs), tickAmount: 4, labels: { formatter: v => Math.round(v) } },
                    { opposite: true, min: 0, max: Math.max(1000, ...trend.collected), tickAmount: 4, title: { text: 'Collected' }, labels: { formatter: v => Number(v).toLocaleString() } },
                ] : [{ min: 0, max: Math.max(4, ...trend.jobs), tickAmount: 4, labels: { formatter: v => Math.round(v) } }],
                legend: { position: 'top', horizontalAlign: 'right' },
                tooltip: { shared: true, intersect: false },
            }).render();
        })();
    </script>
@endsection
