@extends('layouts.vertical', ['subtitle' => 'Handover'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Tailoring', 'subtitle' => 'Handover'])

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Handover Items</h5>
            <p class="card-subtitle mb-0">
                Move items to next stage and track logs.
                <span class="text-muted">Partial handover is supported (Ex: send 2 now, 3 later).</span>
            </p>
        </div>

        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input name="q" class="form-control" value="{{ $q ?? '' }}"
                        placeholder="Search Job No / Batch No">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('tailoring.handover.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>

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
                            <th>Group</th>
                            <th>Completed</th>
                            <th width="260">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($items as $it)
                            @php
                                $groupId = $it->parent_item_id ? $it->parent_item_id : $it->id;
                            @endphp

                            <tr>
                                <td>{{ $it->jobBatch?->job?->job_no ?? '-' }}</td>
                                <td>{{ $it->jobBatch?->batch_no ?? '-' }}</td>
                                <td>{{ $it->jobBatch?->job?->customer?->full_name ?? 'N/A' }}</td>
                                <td>{{ $it->dressType?->name ?? 'N/A' }}</td>

                                <td>
                                    <b>{{ $it->qty }}</b>
                                    @if ($it->parent_item_id)
                                        <span class="badge bg-warning ms-1">Part</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge bg-info">{{ $it->stage?->name ?? 'N/A' }}</span>
                                </td>

                                <td>
                                    <span class="text-muted">#{{ $groupId }}</span>
                                </td>

                                <td>
                                    @if ($it->completed_at)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>

                                <td>
                                    @php
                                        $groupKey = $it->parent_item_id ?: $it->id;
                                    @endphp

                                    <div class="d-flex flex-column gap-2">
                                        <a href="{{ route('tailoring.handover.group.create', $groupKey) }}"
                                            class="btn btn-primary btn-sm w-100">
                                            Group Handover
                                        </a>

                                        <div class="d-flex gap-2">
                                            <a href="{{ route('tailoring.handover.history', $it) }}"
                                                class="btn btn-outline-dark btn-sm w-100">
                                                History
                                            </a>

                                            @if (!$it->completed_at)
                                                <a href="{{ route('tailoring.handover.create', $it) }}"
                                                    class="btn btn-outline-primary btn-sm w-100">
                                                    Single
                                                </a>
                                            @else
                                                <button class="btn btn-secondary btn-sm w-100" disabled>
                                                    Single
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">No items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
