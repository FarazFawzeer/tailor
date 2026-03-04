@extends('layouts.vertical', ['subtitle' => 'Handover'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Tailoring', 'subtitle' => 'Handover'])

    <style>
        .muted { font-size: 12px; color: #6c757d; }
        .stage-pill { border: 1px solid rgba(0,0,0,.08); border-radius: 12px; padding: 10px 12px; background: #fff; }
        .stage-pill .name { font-size: 13px; color: #6c757d; }
        .stage-pill .num { font-size: 20px; font-weight: 700; line-height: 1; }
        .stage-pill .qty { font-size: 12px; color: #6c757d; }

        .job-card { border: 1px solid rgba(0,0,0,.08); border-radius: 12px; overflow: hidden; }
        .job-head { background: rgba(0,0,0,.03); }
        .badge-soft { background: rgba(13,110,253,.08); color: #0d6efd; }
        .table td, .table th { vertical-align: middle; }
        .action-wrap { min-width: 220px; }
    </style>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h5 class="card-title mb-1">Handover Items</h5>
                    <div class="muted">
                        Easy view: <b>Job → Batches/Items</b>. You can do <b>Single</b> or <b>Group Handover</b>.
                        <span class="text-muted">Partial handover supported.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">

            {{-- SEARCH --}}
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input name="q" class="form-control" value="{{ $q ?? '' }}" placeholder="Search Job No / Batch No">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('tailoring.handover.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>

            {{-- STAGE SUMMARY --}}
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-2 mb-3">
                @foreach($stages as $s)
                    @php
                        $sum = $stageSummary[$s->id] ?? ['item_count' => 0, 'qty_sum' => 0];
                    @endphp
                    <div class="col">
                        <div class="stage-pill h-100">
                            <div class="name">{{ $s->name }}</div>
                            <div class="d-flex align-items-end gap-2 mt-1">
                                <div class="num">{{ $sum['item_count'] }}</div>
                                <div class="muted mb-1">items</div>
                            </div>
                            <div class="qty">Qty: <b>{{ $sum['qty_sum'] }}</b></div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- GROUPED JOB VIEW --}}
            @if($groupedJobs->count())
                <div class="accordion" id="handoverJobsAccordion">
                    @php $accIndex = 0; @endphp

                    @foreach($groupedJobs as $jobNo => $jobItems)
                        @php
                            $accIndex++;
                            $first = $jobItems->first();
                            $job = $first->jobBatch?->job;
                            $customer = $job?->customer?->full_name ?? 'N/A';

                            $totalItems = $jobItems->count();
                            $totalQty = (int)$jobItems->sum('qty');
                        @endphp

                        <div class="accordion-item job-card mb-2">
                            <h2 class="accordion-header" id="heading{{ $accIndex }}">
                                <button class="accordion-button {{ $accIndex === 1 ? '' : 'collapsed' }}" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapse{{ $accIndex }}"
                                    aria-expanded="{{ $accIndex === 1 ? 'true' : 'false' }}"
                                    aria-controls="collapse{{ $accIndex }}">
                                    <div class="w-100 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <b>Job: {{ $jobNo }}</b>
                                            <span class="text-muted ms-2">Customer: {{ $customer }}</span>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center">
                                            <span class="badge badge-soft">Items: {{ $totalItems }}</span>
                                            <span class="badge bg-secondary">Qty: {{ $totalQty }}</span>
                                        </div>
                                    </div>
                                </button>
                            </h2>

                            <div id="collapse{{ $accIndex }}" class="accordion-collapse collapse {{ $accIndex === 1 ? 'show' : '' }}"
                                aria-labelledby="heading{{ $accIndex }}" data-bs-parent="#handoverJobsAccordion">
                                <div class="accordion-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:120px;">Batch</th>
                                                    <th>Dress</th>
                                                    <th class="text-end" style="width:80px;">Qty</th>
                                                    <th style="width:140px;">Stage</th>
                                                    <th style="width:110px;">Completed</th>
                                                    <th style="width:100px;">Group</th>
                                                    <th class="action-wrap">Action</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach($jobItems as $it)
                                                    @php
                                                        $groupId = $it->parent_item_id ? $it->parent_item_id : $it->id;
                                                        $groupKey = $it->parent_item_id ?: $it->id;
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-light text-dark">
                                                                {{ $it->jobBatch?->batch_no ?? '-' }}
                                                            </span>
                                                        </td>

                                                        <td>
                                                            <div class="fw-semibold">{{ $it->dressType?->name ?? 'N/A' }}</div>
                                                            <div class="muted">
                                                                Updated: {{ optional($it->updated_at)->format('d M Y, h:i A') }}
                                                                @if($it->parent_item_id)
                                                                    <span class="badge bg-warning ms-1">Partial</span>
                                                                @endif
                                                            </div>
                                                        </td>

                                                        <td class="text-end fw-bold">{{ (int)$it->qty }}</td>

                                                        <td>
                                                            <span class="badge bg-info">{{ $it->stage?->name ?? 'N/A' }}</span>
                                                        </td>

                                                        <td>
                                                            @if($it->completed_at)
                                                                <span class="badge bg-success">Yes</span>
                                                            @else
                                                                <span class="badge bg-secondary">No</span>
                                                            @endif
                                                        </td>

                                                        <td class="text-muted">#{{ $groupId }}</td>

                                                        <td>
                                                            <div class="d-flex flex-wrap gap-2">
                                                                <a href="{{ route('tailoring.handover.group.create', $groupKey) }}"
                                                                    class="btn btn-primary btn-sm">
                                                                    Group Handover
                                                                </a>

                                                                <a href="{{ route('tailoring.handover.history', $it) }}"
                                                                    class="btn btn-outline-dark btn-sm">
                                                                    History
                                                                </a>

                                                                @if(!$it->completed_at)
                                                                    <a href="{{ route('tailoring.handover.create', $it) }}"
                                                                        class="btn btn-outline-primary btn-sm">
                                                                        Single
                                                                    </a>
                                                                @else
                                                                    <button class="btn btn-secondary btn-sm" disabled>Single</button>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end mt-3">
                    {{ $items->links() }}
                </div>
            @else
                <div class="text-center text-muted py-4">
                    No items found.
                </div>
            @endif

        </div>
    </div>
@endsection