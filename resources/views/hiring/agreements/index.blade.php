@extends('layouts.vertical', ['subtitle' => 'Hire Agreements'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Hiring', 'subtitle' => 'Agreements'])

    @php
        $statusMap = [
            ''          => ['label' => 'All Status', 'cls' => 'bg-light text-dark border'],
            'issued'    => ['label' => 'Issued',    'cls' => 'bg-warning text-dark'],
            'returned'  => ['label' => 'Returned',  'cls' => 'bg-success'],
            'cancelled' => ['label' => 'Cancelled', 'cls' => 'bg-secondary'],
        ];
        $currentStatus = $status ?? '';
        $statusBadge = $statusMap[$currentStatus] ?? ['label' => ucfirst($currentStatus), 'cls' => 'bg-secondary'];
    @endphp

    <style>
        .report-card { border:1px solid rgba(0,0,0,.08); border-radius:14px; }
        .muted-help { font-size: 12px; color: #6c757d; }
        .pill { padding: 2px 10px; border-radius: 999px; font-size: 12px; background: rgba(13,110,253,.08); }
        .table thead th { font-size: 12px; text-transform: uppercase; letter-spacing: .03em; color:#6c757d; }
        .btn-icon { display:inline-flex; align-items:center; gap:.35rem; }
    </style>

    <div class="card report-card">
        <div class="card-header d-flex align-items-start justify-content-between">
            <div>
                <h5 class="card-title mb-0">Hire Agreements</h5>
                <div class="muted-help mt-1">
                    Issue / Return agreements and track fine. Current filter:
                    <span class="badge {{ $statusBadge['cls'] }}">{{ $statusBadge['label'] }}</span>
                </div>
            </div>

            <a href="{{ route('hiring.agreements.create') }}" class="btn btn-primary btn-icon">
                <i class="ti ti-plus"></i> Create
            </a>
        </div>

        <div class="card-body">

            {{-- Clean Filters --}}
            <div class=" border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <form method="GET">
                        <div class="row g-2 justify-content-end">

                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="ti ti-search"></i></span>
                                    <input name="q" class="form-control"
                                           value="{{ $q ?? '' }}"
                                           placeholder="Search agreement no / customer name">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    @foreach(['issued','returned','cancelled'] as $st)
                                        <option value="{{ $st }}" {{ ($status ?? '') === $st ? 'selected' : '' }}>
                                            {{ ucfirst($st) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 d-flex gap-2">
                                <button class="btn btn-primary w-100 btn-icon" type="submit">
                                    <i class="ti ti-search"></i> Search
                                </button>
                                <a href="{{ route('hiring.agreements.index') }}" class="btn btn-light border w-100" title="Reset">
                                    <i class="ti ti-refresh"></i>Reset
                                </a>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>Agreement</th>
                        <th>Customer</th>
                        <th>Issue</th>
                        <th>Expected Return</th>
                        <th>Status</th>
                        <th class="text-end">Hire Total</th>
                        <th class="text-end">Fine</th>
                        <th style="width: 280px;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($agreements as $a)
                        @php
                            $badgeCls = match($a->status){
                                'issued' => 'bg-warning text-dark',
                                'returned' => 'bg-success',
                                'cancelled' => 'bg-secondary',
                                default => 'bg-secondary'
                            };
                        @endphp
                        <tr id="row-{{ $a->id }}">
                            <td class="fw-bold">{{ $a->agreement_no }}</td>
                            <td>{{ $a->customer?->full_name ?? 'N/A' }}</td>
                            <td>{{ optional($a->issue_date)->format('d M Y') }}</td>
                            <td>{{ optional($a->expected_return_date)->format('d M Y') }}</td>
                            <td><span class="badge {{ $badgeCls }}" style="width: 75px;">{{ ucfirst($a->status) }}</span></td>
                            <td class="text-end fw-semibold">{{ number_format((float)$a->total_hire_amount, 2) }}</td>
                            <td class="text-end fw-semibold">{{ number_format((float)$a->fine_amount, 2) }}</td>

                            <td>
                                <div class="d-flex gap-2">
                                    {{-- View --}}
                                    <a href="{{ route('hiring.agreements.show', $a->id) }}"
                                       class="btn btn-outline-primary btn-sm w-100 btn-icon justify-content-center align-items-cente">
                                        <i class="ti ti-eye"></i> View
                                    </a>

                                    {{-- Edit (only if not returned) --}}
                                    @if($a->status !== 'returned')
                                        <a href="{{ route('hiring.agreements.edit', $a->id) }}"
                                           class="btn btn-outline-dark btn-sm w-100 btn-icon justify-content-center align-items-cente">
                                            <i class="ti ti-edit"></i> Edit
                                        </a>
                                    @else
                                        <button class="btn btn-outline-dark btn-sm w-100" disabled title="Returned agreements cannot be edited">
                                            <i class="ti ti-edit"></i> Edit
                                        </button>
                                    @endif

                                    {{-- Delete (only if cancelled OR issued, you can adjust rule) --}}
                                    @if($a->status !== 'returned')
                                        <button type="button"
                                                class="btn btn-danger btn-sm w-100 btn-icon btn-delete justify-content-center align-items-cente"
                                                data-id="{{ $a->id }}">
                                            <i class="ti ti-trash"></i> Delete
                                        </button>
                                    @else
                                        <button class="btn btn-danger btn-sm w-100" disabled title="Returned agreements cannot be deleted">
                                            <i class="ti ti-trash"></i> Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No agreements found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $agreements->links() }}
            </div>
        </div>
    </div>

    <script>
        // Delete Agreement (AJAX)
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;

                Swal.fire({
                    title: 'Delete this agreement?',
                    text: "You can't undo this.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete'
                }).then((r) => {
                    if (!r.isConfirmed) return;

                    fetch("{{ url('/hiring/agreements/delete') }}/" + id, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        }
                    }).then(async res => {
                        const data = await res.json().catch(() => ({}));

                        if (!res.ok) {
                            Swal.fire('Error', data.message || 'Something went wrong', 'error');
                            return;
                        }

                        document.getElementById('row-' + id)?.remove();
                        Swal.fire('Deleted', data.message || 'Agreement deleted', 'success');
                    }).catch(() => {
                        Swal.fire('Error', 'Network error. Please try again.', 'error');
                    });
                });
            });
        });
    </script>
@endsection