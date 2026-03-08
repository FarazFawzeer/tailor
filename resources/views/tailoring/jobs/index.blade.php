@extends('layouts.vertical', ['subtitle' => 'Tailoring Jobs'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Tailoring Jobs', 'subtitle' => 'Jobs List'])

    <style>
        .muted-help { font-size: 12px; color: #6c757d; }
        .pill { padding: 2px 10px; border-radius: 999px; font-size: 12px; background: rgba(13,110,253,.08); }
        .table td, .table th { vertical-align: middle; }
        .actions { min-width: 190px; }
        @media (max-width: 768px){
            .actions { min-width: 140px; }
        }
        .money { font-weight: 700; }
        .badge-soft { background: rgba(13,110,253,.10); color: #0d6efd; border:1px solid rgba(13,110,253,.15); }
    </style>

    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="card-title mb-0">Jobs</h5>
                    <div class="muted-help">View / Edit tailoring jobs  (Job → Batches → Items).</div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('tailoring.jobs.createWizard') }}" class="btn btn-primary">
                        + Create Job
                    </a>
                </div>
            </div>
        </div>
@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Success',
    text: '{{ session('success') }}',
    timer: 2000,
    showConfirmButton: false
});
</script>
@endif
        <div class="card-body">
            {{-- Search --}}
           {{-- Search --}}
<div class="d-flex justify-content-end mb-3">
    <form method="GET" action="{{ route('tailoring.jobs.index') }}" class="d-flex gap-2">

        <div class="input-group" style="width:320px;">
            <span class="input-group-text bg-white">
                <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
            </span>

            <input type="text"
                name="q"
                class="form-control"
                value="{{ request('q') }}"
                placeholder="Search Jobs...">
        </div>

        <button class="btn btn-dark">
            Search
        </button>

        <a class="btn btn-outline-secondary"
           href="{{ route('tailoring.jobs.index') }}">
            Reset
        </a>

    </form>
</div>
            <div class="table-responsive">
                <table class="table table-hover table-centered">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 120px;">Job No</th>
                            <th style="min-width: 220px;">Customer</th>
                            <th style="min-width: 120px;">Job Date</th>
                            <th style="min-width: 120px;">Due Date</th>

                            {{-- ✅ NEW --}}
                            <th style="min-width: 140px;">Status</th>

                            
                            <th style="min-width: 200px;">Progress</th>

                            {{-- ✅ NEW --}}
                            <th style="min-width: 140px;" class="text-end">Total Amount</th>

                            <th class="actions">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($jobs as $j)
                          @php
    $totalQty = (int)($j->total_qty ?? 0);
    $completedQty = (int)($j->completed_qty ?? 0);
    $deliveredQty = (int)($j->delivered_qty ?? 0);

    // Treat Delivered stage as completed for listing
    $doneQty = $completedQty + $deliveredQty;

    if ($doneQty > $totalQty) {
        $doneQty = $totalQty;
    }

    $progressPercent = $totalQty > 0 ? (int)round(($doneQty / $totalQty) * 100) : 0;

    if ($totalQty === 0) {
        $statusText = 'No Items';
        $statusBadge = 'bg-secondary';
    } elseif ($doneQty >= $totalQty) {
        $statusText = 'Completed';
        $statusBadge = 'bg-success';
    } elseif ($doneQty > 0) {
        $statusText = 'In Progress';
        $statusBadge = 'bg-warning';
    } else {
        $statusText = 'Pending';
        $statusBadge = 'bg-danger';
    }

    $amount = (float)($j->total_amount ?? 0);
@endphp

                            <tr>
                                <td>
                                    <div><b>{{ $j->job_no }}</b></div>
                                    {{-- <div class="muted-help">ID: {{ $j->id }}</div> --}}
                                </td>

                                <td>
                                    <div><b>{{ $j->customer?->full_name ?? '-' }}</b></div>
                                    <div class="muted-help">{{ $j->customer?->phone ?? '-' }}</div>
                                </td>

                                <td>{{ $j->job_date?->format('d M Y') ?? '-' }}</td>
                                <td>{{ $j->due_date?->format('d M Y') ?? '-' }}</td>

                                {{-- ✅ STATUS --}}
                                <td>
                                    <span class="badge {{ $statusBadge }}" style="width: 75px;">{{ $statusText }}</span>
                                </td>

                               
                                {{-- ✅ PROGRESS --}}
                                <td>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <span class="pill">Batches: {{ $j->batches_count ?? 0 }}</span>
                                        <span class="pill">Items: {{ $j->items_count ?? 0 }}</span>
      <span class="pill">Done: {{ $doneQty }}/{{ $totalQty }} ({{ $progressPercent }}%)</span>
                                    </div>

                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar" role="progressbar"
                                            style="width: {{ $progressPercent }}%;"
                                            aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </td>

                                {{-- ✅ TOTAL AMOUNT --}}
                                <td class="text-end">
                                    <div class="money">{{ number_format($amount, 2) }}</div>
                                    <div class="muted-help">LKR</div>
                                </td>

   <td>
    <div class="d-flex gap-2">

        <a class="btn btn-outline-dark btn-sm w-100"
           href="{{ route('tailoring.jobs.show', $j) }}">
            View
        </a>

        <a class="btn btn-primary btn-sm w-100"
           href="{{ route('tailoring.jobs.editWizard', $j) }}">
            Edit
        </a>

        <form action="{{ route('tailoring.jobs.destroy', $j) }}" method="POST" class="delete-form w-100">
            @csrf
            @method('DELETE')

            <button type="button" class="btn btn-outline-danger btn-sm w-100 btnDelete">
                Delete
            </button>
        </form>

    </div>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No jobs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    {{ $jobs->links() }}
                </div>
            </div>
        </div>
    </div>


    <script>

document.querySelectorAll('.btnDelete').forEach(btn => {

    btn.addEventListener('click', function () {

        let form = this.closest('.delete-form');

        Swal.fire({
            title: 'Delete Job?',
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (result.isConfirmed) {
                form.submit();
            }

        });

    });

});

</script>
@endsection