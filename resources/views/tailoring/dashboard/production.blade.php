@extends('layouts.vertical', ['subtitle' => 'Production Dashboard'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Tailoring', 'subtitle' => 'Production Dashboard'])

    {{-- TOP CARDS --}}
    <div class="row">
        @foreach($stages as $s)
            @php
                $count = (int)($stageCounts[$s->id] ?? 0);
                $qty   = (int)($stageQtyCounts[$s->id] ?? 0);
                $isLast = $lastStage && $lastStage->id === $s->id;
            @endphp

            <div class="col-md-6 col-xl-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">{{ $s->name }}</p>
                                <h3 class="mb-0">{{ $count }}</h3>
                                <small class="text-muted">Total Qty: {{ $qty }}</small>
                            </div>
                            <div class="avatar-md bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <iconify-icon icon="{{ $isLast ? 'solar:box-outline' : 'solar:scissors-outline' }}"
                                    class="fs-32 text-primary"></iconify-icon>
                            </div>
                        </div>

                        @if($isLast)
                            <div class="mt-2">
                                <span class="badge bg-success">Ready for Delivery Stage</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- SECOND ROW --}}
    <div class="row">
        {{-- Ready + Overdue --}}
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Ready for Delivery Items</span>
                        <b>{{ $readyForDeliveryCount }}</b>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Ready for Delivery Qty</span>
                        <b>{{ $readyForDeliveryQty }}</b>
                    </div>

                    @if($hasDueDate)
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="text-danger">Overdue Items</span>
                            <b class="text-danger">{{ $overdueCount }}</b>
                        </div>
                        <small class="text-muted">Based on jobs.due_date</small>
                    @else
                        <div class="alert alert-info mb-0">
                            Overdue tracking disabled (jobs.due_date column not found).
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Staff workload (optional) --}}
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Staff Workload (Optional)</h5>
                    <p class="card-subtitle mb-0">Shows only if handover_logs table exists.</p>
                </div>
                <div class="card-body">
                    @if($staffWorkload->count())
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Staff</th>
                                        <th>Stage</th>
                                        <th>Handovers</th>
                                        <th>Total Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($staffWorkload as $w)
                                        <tr>
                                            <td>{{ $w->staff_name ?? 'N/A' }}</td>
                                            <td>{{ $w->stage_name ?? 'N/A' }}</td>
                                            <td>{{ $w->handovers }}</td>
                                            <td>{{ $w->total_qty ?? 0 }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-secondary mb-0">
                            Workload data not available yet (handover logs not created).
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Overdue list --}}
    @if($hasDueDate && $overdueItems->count())
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0 text-danger">Overdue Items (Top 10)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-centered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Job</th>
                                <th>Batch</th>
                                <th>Customer</th>
                                <th>Dress</th>
                                <th>Qty</th>
                                <th>Stage</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overdueItems as $o)
                                <tr>
                                    <td>{{ $o->job_no }}</td>
                                    <td>{{ $o->batch_no }}</td>
                                  <td>{{ $o->full_name ?? 'N/A' }}</td>
                                    <td>{{ $o->dress_name ?? 'N/A' }}</td>
                                    <td>{{ $o->qty }}</td>
                                    <td><span class="badge bg-warning">{{ $o->stage_name ?? 'N/A' }}</span></td>
                                    <td class="text-danger">{{ \Carbon\Carbon::parse($o->due_date)->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Latest items --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Latest Production Items</h5>
            <p class="card-subtitle mb-0">Recently updated items (by updated_at).</p>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Job</th>
                            <th>Batch</th>
                            <th>Customer</th>
                            <th>Dress</th>
                            <th>Qty</th>
                            <th>Stage</th>
                            <th>Due</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestItems as $it)
                            <tr>
                                <td>{{ $it->job_no }}</td>
                                <td>{{ $it->batch_no }}</td>
                           <td>{{ $it->full_name ?? 'N/A' }}</td>
                                <td>{{ $it->dress_name ?? 'N/A' }}</td>
                                <td>{{ $it->qty }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $it->stage_name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    @if(!empty($it->due_date))
                                        {{ \Carbon\Carbon::parse($it->due_date)->format('d M Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($it->updated_at)->format('d M Y, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No production items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection