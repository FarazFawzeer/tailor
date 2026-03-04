@extends('layouts.vertical', ['subtitle' => 'Hire Items'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Hiring Inventory', 'subtitle' => 'Items'])

    @php
        $statusMap = [
            ''            => ['label' => 'All Status',  'cls' => 'bg-light text-dark border'],
            'available'   => ['label' => 'Available',   'cls' => 'bg-success'],
            'reserved'    => ['label' => 'Reserved',    'cls' => 'bg-warning text-dark'],
            'hired'       => ['label' => 'Hired',       'cls' => 'bg-primary'],
            'maintenance' => ['label' => 'Maintenance', 'cls' => 'bg-danger'],
        ];
        $currentStatus = $status ?? '';
        $statusBadge = $statusMap[$currentStatus] ?? ['label' => ucfirst($currentStatus), 'cls' => 'bg-secondary'];
    @endphp

    <style>
        .report-card { border:1px solid rgba(0,0,0,.08); border-radius:14px; }
        .muted-help { font-size: 12px; color: #6c757d; }
        .pill { padding: 2px 10px; border-radius: 999px; font-size: 12px; background: rgba(13,110,253,.08); }
        .thumb { width: 42px; height: 42px; border-radius: 10px; object-fit: cover; border:1px solid rgba(0,0,0,.08); }
        .table thead th { font-size: 12px; text-transform: uppercase; letter-spacing: .03em; color:#6c757d; }
        .filters { background: rgba(13,110,253,.03); border:1px solid rgba(13,110,253,.10); border-radius: 14px; }
        .btn-icon { display:inline-flex; align-items:center; gap:.35rem; }
    </style>

    <div class="card report-card">
        <div class="card-header d-flex align-items-start justify-content-between">
            <div>
                <h5 class="card-title mb-0">Hire Items</h5>
                <div class="muted-help mt-1">
                    Search & filter your hiring inventory. Current filter:
                    <span class="badge {{ $statusBadge['cls'] }}">{{ $statusBadge['label'] }}</span>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('hiring.items.create') }}" class="btn btn-primary btn-icon">
                    <i class="ti ti-plus"></i> Create
                </a>
            </div>
        </div>

        <div class="card-body">
            {{-- Filters --}}
      {{-- Filters --}}
<div class="mb-3">
    <div class="card-body py-3">

        <form method="GET">
            <div class="row g-2 justify-content-end">

                {{-- Search --}}
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="ti ti-search"></i>
                        </span>
                        <input
                            type="text"
                            name="q"
                            class="form-control"
                            value="{{ $q ?? '' }}"
                            placeholder="Search item code, name or category">
                    </div>
                </div>

                {{-- Status --}}
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        @foreach(['available','reserved','hired','maintenance'] as $st)
                            <option value="{{ $st }}" {{ ($status ?? '') === $st ? 'selected' : '' }}>
                                {{ ucfirst($st) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-search me-1"></i> Search
                    </button>

                    <a href="{{ route('hiring.items.index') }}" class="btn btn-light border w-100">
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
                        <th>Item</th>
                        <th>Code</th>
                        <th>Category</th>
                        <th class="text-center">Sizes</th>
                        <th class="text-center">Total Qty</th>
                        <th class="text-end">Price</th>
                        <th>Status</th>
                        <th style="width: 260px;">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($items as $it)
                        @php
                            $imgPath = $it->images->first()?->image_path
                                ? asset($it->images->first()->image_path)
                                : asset('/images/users/avatar-6.jpg');

                            $variants = $it->variants ?? collect();     // requires ->with('variants') in controller
                            $sizesCount = $variants->count();
                            $totalQty = (int) $variants->sum('qty');

                            $badgeCls = match($it->status){
                                'available' => 'bg-success',
                                'reserved' => 'bg-warning text-dark',
                                'hired' => 'bg-primary',
                                'maintenance' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                        @endphp

                        <tr id="row-{{ $it->id }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img class="thumb" src="{{ $imgPath }}" alt="img">
                                    <div>
                                        <div class="fw-bold">{{ $it->name }}</div>
                                        <div class="text-muted small">
                                            {{ $it->notes ? \Illuminate\Support\Str::limit($it->notes, 55) : '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="fw-bold">{{ $it->item_code }}</td>
                            <td>{{ $it->category ?? '-' }}</td>

                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $sizesCount }}</span>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ number_format($totalQty) }}</span>
                            </td>

                            <td class="text-end fw-semibold">{{ number_format((float)$it->hire_price, 2) }}</td>

                            <td>
                                <span class="badge {{ $badgeCls }}" style="width: 75px;">{{ ucfirst($it->status) }}</span>
                                @if(!$it->is_active)
                                    <div class="text-muted small">Inactive</div>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex gap-2">
                                    {{-- VIEW --}}
                                    <a href="{{ route('hiring.items.show', $it->id) }}"
                                       class="btn btn-outline-primary btn-sm w-100 btn-icon">
                                        <i class="ti ti-eye"></i> View
                                    </a>

                                    {{-- EDIT --}}
                                    <a href="{{ route('hiring.items.edit', $it->id) }}"
                                       class="btn btn-outline-dark btn-sm w-100 btn-icon">
                                        <i class="ti ti-edit"></i> Edit
                                    </a>

                                    {{-- DELETE --}}
                                    <button type="button"
                                            class="btn btn-danger btn-sm w-100 btn-delete btn-icon"
                                            data-id="{{ $it->id }}">
                                        <i class="ti ti-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No items found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $items->links() }}
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;

                Swal.fire({
                    title: 'Delete this item?',
                    text: "You can't undo this.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete'
                }).then((r) => {
                    if (!r.isConfirmed) return;

                    fetch("{{ url('hiring/items') }}/" + id, {
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
                        Swal.fire('Deleted', data.message || 'Item deleted', 'success');
                    }).catch(() => {
                        Swal.fire('Error', 'Network error. Please try again.', 'error');
                    });
                });
            });
        });
    </script>
@endsection