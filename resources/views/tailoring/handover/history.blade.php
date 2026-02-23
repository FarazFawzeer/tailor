@extends('layouts.vertical', ['subtitle' => 'Handover History'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Handover', 'subtitle' => 'History'])

    @php
        $jobNo   = $item->jobBatch?->job?->job_no ?? '-';
        $batchNo = $item->jobBatch?->batch_no ?? '-';
        $customer = $item->jobBatch?->job?->customer?->full_name ?? 'N/A';
        $dress = $item->dressType?->name ?? 'N/A';

        $groupId = $item->parent_item_id ? $item->parent_item_id : $item->id;
        $isPartial = (bool)$item->parent_item_id;
    @endphp

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-1">
                Job: {{ $jobNo }} | Batch: {{ $batchNo }}
                @if($isPartial)
                    <span class="badge bg-warning ms-2">Partial Item</span>
                @endif
            </h5>

            <p class="card-subtitle mb-0">
                Customer: <b>{{ $customer }}</b> |
                Dress: <b>{{ $dress }}</b> |
                Item Qty: <b>{{ $item->qty }}</b>
                <span class="text-muted">| Group: #{{ $groupId }}</span>
            </p>
        </div>

        <div class="card-body">
            @if($item->completed_at)
                <div class="alert alert-success">
                    ✅ Completed on <b>{{ $item->completed_at?->format('d M Y, h:i A') }}</b>
                </div>
            @else
                <div class="alert alert-secondary">
                    ⏳ Not completed yet. Current Stage: <b>{{ $item->stage?->name ?? 'N/A' }}</b>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>From Stage</th>
                            <th>To Stage</th>
                            <th>Qty Moved</th>
                            <th>Handed By</th>
                            <th>Received By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $l)
                            <tr>
                                <td>{{ $l->handover_at?->format('d M Y, h:i A') ?? '-' }}</td>
                                <td>{{ $l->fromStage?->name ?? '-' }}</td>
                                <td>
                                    @if($l->to_stage_id)
                                        <span class="badge bg-info">{{ $l->toStage?->name ?? '-' }}</span>
                                    @else
                                        <span class="badge bg-success">Completed</span>
                                    @endif
                                </td>
                                <td><b>{{ $l->qty }}</b></td>
                                <td>{{ $l->handedBy?->name ?? '-' }}</td>
                                <td>{{ $l->receivedBy?->name ?? '-' }}</td>
                                <td class="text-muted">{{ $l->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No handover records.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('tailoring.handover.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection