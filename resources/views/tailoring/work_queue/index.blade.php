@extends('layouts.vertical', ['subtitle' => 'Work Queue'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Tailoring', 'subtitle' => 'Work Queue'])

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">My Work Queue</h5>
            <p class="card-subtitle mb-0">
                This page shows <b>grouped pending qty</b> for your stage. Use <b>Group Handover</b> to move remaining qty
                without affecting other stages.
            </p>
        </div>

        <div class="card-body">
            {{-- Filters --}}
            <form method="GET" action="{{ route('tailoring.workqueue.index') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" name="q" class="form-control" value="{{ $q ?? '' }}"
                        placeholder="Search Job No / Batch No / Customer / Dress">
                </div>

                @if($canViewAll)
                    <div class="col-md-3">
                        <select name="stage_id" class="form-select">
                            <option value="">All Stages</option>
                            @foreach($stages as $s)
                                <option value="{{ $s->id }}" {{ (string)$selectedStageId === (string)$s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>

                <div class="col-md-2">
                    <a href="{{ route('tailoring.workqueue.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Job</th>
                            <th>Batch</th>
                            <th>Customer</th>
                            <th>Dress</th>
                            <th>Stage</th>
                            <th>Available Qty</th>
                            <th>Last Updated</th>
                            <th style="width: 320px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($items as $it)
                            <tr>
                                <td><b>{{ $it->job_no ?? '-' }}</b></td>
                                <td>{{ $it->batch_no ?? '-' }}</td>
                                <td>{{ $it->customer_name ?? 'N/A' }}</td>
                                <td>{{ $it->dress_name ?? 'N/A' }}</td>

                                <td>
                                    <span class="badge bg-info">{{ $it->stage_name ?? 'N/A' }}</span>
                                    <div class="small text-muted">Group #{{ $it->group_id }}</div>
                                </td>

                                <td>
                                    <span class="fs-5 fw-bold">{{ $it->total_qty }}</span>
                                    <div class="small text-muted">Qty you can move now</div>
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($it->last_updated_at)->format('d M Y, h:i A') }}
                                </td>

                                <td>
                                    <div class="d-flex gap-2">
                                        {{-- Group Handover (recommended) --}}
                                        <a href="{{ route('tailoring.handover.group.create', $it->group_id) }}"
                                           class="btn btn-primary btn-sm w-100">
                                            Group Handover
                                        </a>

                                        {{-- Optional: open old item-level list by filtering handover page --}}
                                        <a href="{{ route('tailoring.handover.index', ['q' => $it->job_no]) }}"
                                           class="btn btn-outline-dark btn-sm w-100">
                                            View in Handover
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    No items in your queue.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection