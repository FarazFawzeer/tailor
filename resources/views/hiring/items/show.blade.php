@extends('layouts.vertical', ['subtitle' => 'Hire Item View'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Hiring Inventory', 'subtitle' => 'View'])

@php
    // Safe totals
    $variants = $item->variants ?? collect();
    $images   = $item->images ?? collect();
    $totalQty = (int) $variants->sum('qty');

    $statusMap = [
        'available'   => ['label' => 'Available',   'cls' => 'bg-success'],
        'reserved'    => ['label' => 'Reserved',    'cls' => 'bg-warning text-dark'],
        'hired'       => ['label' => 'Hired',       'cls' => 'bg-primary'],
        'maintenance' => ['label' => 'Maintenance', 'cls' => 'bg-danger'],
    ];

    $status = $statusMap[$item->status] ?? ['label' => ucfirst($item->status ?? 'N/A'), 'cls' => 'bg-secondary'];
@endphp

<style>
    .report-card { border:1px solid rgba(0,0,0,.08); border-radius:14px; }
    .muted-help { font-size: 12px; color: #6c757d; }
    .pill { padding: 4px 10px; border-radius: 999px; font-size: 12px; background: rgba(13,110,253,.08); }
    .stat { border:1px solid rgba(0,0,0,.08); border-radius:12px; padding:12px; }
    .kv { border:1px solid rgba(0,0,0,.06); border-radius:12px; padding:14px; }
    .kv .k { font-size: 12px; color:#6c757d; }
    .kv .v { font-weight: 600; }
    .thumb { width:100%; height:140px; object-fit:cover; border-radius:12px; border:1px solid rgba(0,0,0,.08); }
    .table thead th { font-size: 12px; text-transform: uppercase; letter-spacing: .03em; color:#6c757d; }
</style>

<div class="row g-3">

    {{-- Left: Details --}}
    <div class="col-lg-8">
        <div class="card report-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0">{{ $item->name }}</h5>
                    <div class="muted-help mt-1">
                        Item Code: <span class="pill">{{ $item->item_code }}</span>
                        <span class="ms-2 badge {{ $status['cls'] }}">{{ $status['label'] }}</span>
                        @if($item->is_active)
                            <span class="ms-2 badge bg-success-subtle text-success border border-success-subtle">Active</span>
                        @else
                            <span class="ms-2 badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                        @endif
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('hiring.items.edit', $item->id) }}" class="btn btn-outline-primary">
                        <i class="ti ti-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('hiring.items.index') }}" class="btn btn-secondary">
                        Back
                    </a>
                </div>
            </div>

            <div class="card-body">

                {{-- Quick Stats --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <div class="stat">
                            <div class="muted-help">Total Qty</div>
                            <div class="fs-5 fw-bold">{{ number_format($totalQty) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat">
                            <div class="muted-help">Sizes</div>
                            <div class="fs-5 fw-bold">{{ number_format($variants->count()) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat">
                            <div class="muted-help">Hire Price</div>
                            <div class="fs-5 fw-bold">{{ number_format((float)$item->hire_price, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat">
                            <div class="muted-help">Deposit</div>
                            <div class="fs-5 fw-bold">{{ number_format((float)($item->deposit_amount ?? 0), 2) }}</div>
                        </div>
                    </div>
                </div>

                {{-- Key Info Grid --}}
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="kv">
                            <div class="k">Category</div>
                            <div class="v">{{ $item->category ?: '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="kv">
                            <div class="k">Default Color</div>
                            <div class="v">{{ $item->color ?: '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="kv">
                            <div class="k">Created</div>
                            <div class="v">{{ optional($item->created_at)->format('Y-m-d h:i A') ?: '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="kv">
                            <div class="k">Updated</div>
                            <div class="v">{{ optional($item->updated_at)->format('Y-m-d h:i A') ?: '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="kv">
                            <div class="k">Notes</div>
                            <div class="v fw-normal">{{ $item->notes ?: '—' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Variants Table --}}
                <div class="mt-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="mb-0">Sizes & Quantities</h6>
                        <span class="muted-help">Total: {{ number_format($totalQty) }}</span>
                    </div>
                    <hr class="mt-2 mb-3">

                    @if($variants->count())
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 30%">Size</th>
                                        <th style="width: 20%">Color</th>
                                        <th style="width: 20%">Qty</th>
                                        <th style="width: 30%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($variants as $v)
                                        <tr>
                                            <td class="fw-semibold">{{ $v->size }}</td>
                                            <td>{{ $v->color ?: '—' }}</td>
                                            <td class="fw-bold">{{ number_format((int)$v->qty) }}</td>
                                            <td>
                                                @if($v->is_active ?? true)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">No size/quantity rows found for this item.</div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Right: Images --}}
    <div class="col-lg-4">
        <div class="card report-card">
            <div class="card-header">
                <h6 class="mb-0">Images</h6>
                <div class="muted-help">Click to open full image.</div>
            </div>
            <div class="card-body">
                @if($images->count())
                    <div class="row g-2">
                        @foreach($images as $img)
                            <div class="col-6">
                                <a href="{{ asset($img->image_path) }}" target="_blank" class="text-decoration-none">
                                    <img src="{{ asset($img->image_path) }}" class="thumb" alt="Image">
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-light border mb-0">
                        No images uploaded for this item.
                    </div>
                @endif
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card report-card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('hiring.items.edit', $item->id) }}" class="btn btn-outline-primary w-100">
                    <i class="ti ti-edit me-1"></i>Edit Item
                </a>

                {{-- OPTIONAL: if you have hire/reserve pages later --}}
                {{-- <a href="#" class="btn btn-outline-success w-100">Hire Now</a> --}}
                {{-- <a href="#" class="btn btn-outline-warning w-100">Reserve</a> --}}
            </div>
        </div>
    </div>

</div>
@endsection