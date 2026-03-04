{{-- resources/views/hiring/agreements/return.blade.php --}}

@extends('layouts.vertical', ['subtitle' => 'Return Items'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Hiring', 'subtitle' => 'Return Items'])

    @php
        $expected   = $agreement->expected_return_date;
        $finePerDay = (float)($agreement->fine_per_day ?? 0);

        $lines = $agreement->items ?? collect();

        $totalQty  = (int)$lines->sum('qty');
        $hireTotal = (float)$lines->sum('line_total');

        $deposit = (float)($agreement->deposit_received ?? 0);
        $paid    = (float)($agreement->amount_paid ?? 0); // ✅ you should have this column

        $pendingBeforeFine = max(0, $hireTotal - $deposit - $paid);

        $statusBadge = match($agreement->status){
            'issued' => 'bg-warning text-dark',
            'returned' => 'bg-success',
            'cancelled' => 'bg-secondary',
            default => 'bg-secondary'
        };
    @endphp

    <style>
        .report-card { border:1px solid rgba(0,0,0,.08); border-radius:14px; }
        .muted-help { font-size: 12px; color: #6c757d; }
        .thumb { width:42px; height:42px; border-radius:12px; object-fit:cover; border:1px solid rgba(0,0,0,.08); }
        .kpi { border:1px solid rgba(0,0,0,.08); border-radius:14px; padding:12px; background:#fff; }
        .kpi .label { font-size:12px; color:#6c757d; }
        .kpi .val { font-weight:700; font-size:16px; }
        .btn-icon { display:inline-flex; align-items:center; gap:.35rem; }
        .table thead th { font-size: 12px; text-transform: uppercase; letter-spacing: .03em; color:#6c757d; }
    </style>

    <div class="card report-card">
        <div class="card-header d-flex align-items-start justify-content-between">
            <div>
                <h5 class="card-title mb-0">Return Items - {{ $agreement->agreement_no }}</h5>
                <div class="muted-help mt-1">
                    Customer: <b>{{ $agreement->customer?->full_name ?? 'N/A' }}</b>
                    <span class="text-muted">|</span>
                    Status: <span class="badge {{ $statusBadge }}">{{ ucfirst($agreement->status) }}</span>
                </div>
            </div>

            <a href="{{ route('hiring.agreements.show', $agreement) }}"
               class="btn btn-light border btn-icon d-flex justify-content-center align-items-center"
               style="width:150px;">
                <i class="ti ti-arrow-left"></i> Back
            </a>
        </div>

        <div class="card-body">
            <div id="message"></div>

            {{-- Top KPIs --}}
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <div class="kpi">
                        <div class="label">Expected Return Date</div>
                        <div class="val">{{ optional($expected)->format('d M Y') ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="kpi">
                        <div class="label">Fine Per Day</div>
                        <div class="val">Rs {{ number_format($finePerDay, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="kpi">
                        <div class="label">Agreement Items</div>
                        <div class="val">{{ $lines->count() }} lines (Qty: {{ $totalQty }})</div>
                        <div class="muted-help">Each size is shown as a line</div>
                    </div>
                </div>
            </div>

            {{-- Items Preview --}}
            <div class="card border mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <b>Items to Return</b>
                    <div class="text-muted small">
                        Hire Total: <b>Rs {{ number_format($hireTotal, 2) }}</b>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-centered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Code</th>
                                    <th>Size</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($agreement->items as $ai)
                                    @php
                                        $thumb = $ai->item?->images?->first()?->image_path
                                            ? asset($ai->item->images->first()->image_path)
                                            : asset('/images/users/avatar-6.jpg');

                                        $price = (float)($ai->hire_price ?? 0);
                                        $qty   = (int)($ai->qty ?? 1);
                                        $lt    = (float)($ai->line_total ?? ($price * $qty));
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img class="thumb" src="{{ $thumb }}" alt="img">
                                                <div>
                                                    <div class="fw-bold">{{ $ai->item?->name ?? 'N/A' }}</div>
                                                    <div class="text-muted small">{{ $ai->item?->category ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-bold">{{ $ai->item?->item_code ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $ai->size ?? '-' }}</span>
                                        </td>
                                        <td class="text-end">Rs {{ number_format($price, 2) }}</td>
                                        <td class="text-end fw-semibold">{{ $qty }}</td>
                                        <td class="text-end fw-semibold">Rs {{ number_format($lt, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Totals</th>
                                    <th class="text-end">{{ $totalQty }}</th>
                                    <th class="text-end">Rs {{ number_format($hireTotal, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <form id="returnForm">
                @csrf

                {{-- Return Details --}}
                <div class="card border mb-3">
                    <div class="card-header">
                        <b>Return Details</b>
                        <div class="muted-help">Select the actual return date. Fine will be calculated automatically.</div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label mb-1">Actual Return Date <span class="text-danger">*</span></label>
                                <input type="date" name="actual_return_date" id="actual_return_date" class="form-control"
                                       value="{{ now()->toDateString() }}" required>
                                <div class="muted-help mt-1">Fine applies if returned after expected date.</div>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label mb-1">Notes (optional)</label>
                                <input name="notes" class="form-control" placeholder="Any notes about return...">
                            </div>
                        </div>

                        <div class="alert alert-info mt-3 mb-0">
                            On confirm, system will:
                            <b>restore qty to each size</b> and calculate <b>fine</b> (if late).
                        </div>
                    </div>
                </div>

                {{-- Payment --}}
                <div class="card border mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <b>Payment</b>
                        <span class="badge bg-light text-dark border">
                            Pending (Before Fine): Rs <span id="pendingAmtText">{{ number_format($pendingBeforeFine, 2) }}</span>
                        </span>
                    </div>

                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <div class="kpi">
                                    <div class="label">Hire Total</div>
                                    <div class="val">Rs {{ number_format($hireTotal, 2) }}</div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="kpi">
                                    <div class="label">Deposit Received</div>
                                    <div class="val">Rs {{ number_format($deposit, 2) }}</div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="kpi">
                                    <div class="label">Already Paid</div>
                                    <div class="val">Rs {{ number_format($paid, 2) }}</div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="kpi">
                                    <div class="label">Pending</div>
                                    <div class="val text-danger">Rs {{ number_format($pendingBeforeFine, 2) }}</div>
                                    <div class="muted-help">Fine (if late) will be added when confirm.</div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label mb-1">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="bank">Bank Transfer</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label mb-1">Pay Now (Rs)</label>
                                <input type="number" step="0.01" min="0"
                                       class="form-control" name="pay_now" id="pay_now"
                                       value="{{ $pendingBeforeFine > 0 ? number_format($pendingBeforeFine, 2, '.', '') : '0.00' }}">
                                <div class="muted-help">If pending exists, you must pay full pending amount.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label mb-1">Reference No (optional)</label>
                                <input type="text" class="form-control" name="payment_reference" placeholder="Receipt / Ref no">
                            </div>
                        </div>

                        <div class="alert alert-warning mt-3 mb-0" id="pendingWarn" style="display:none;">
                            Pending amount must be fully paid before returning items.
                        </div>

                        <input type="hidden" id="pendingBeforeFine" value="{{ number_format($pendingBeforeFine, 2, '.', '') }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('hiring.agreements.show', $agreement) }}"
                       class="btn btn-secondary btn-icon d-flex justify-content-center align-items-center"
                       style="width: 150px;">
                        <i class="ti ti-arrow-left"></i> Back
                    </a>

                    <button class="btn btn-success btn-icon d-flex justify-content-center align-items-center"
                            style="width: 180px;" type="submit">
                        <i class="ti ti-check"></i> Confirm Return
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('returnForm').addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Confirm Return?',
                text: 'This will restore stock and calculate fine.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, confirm',
                cancelButtonText: 'Cancel'
            }).then((r) => {
                if(!r.isConfirmed) return;

                const pending = parseFloat(document.getElementById('pendingBeforeFine').value || "0");
                const payNow  = parseFloat((document.getElementById('pay_now').value || "0"));
                const warn = document.getElementById('pendingWarn');

                if (pending > 0) {
                    if (isNaN(payNow) || payNow < pending) {
                        warn.style.display = 'block';
                        Swal.fire('Payment Required', 'Please pay the full pending amount before confirming return.', 'warning');
                        return;
                    }
                }
                warn.style.display = 'none';

                const fd = new FormData(document.getElementById('returnForm'));
                const msg = document.getElementById('message');

                fetch("{{ route('hiring.agreements.return.store', $agreement) }}", {
                    method: "POST",
                    body: fd,
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    }
                }).then(async res => {
                    const data = await res.json().catch(() => ({}));

                    if (!res.ok) {
                        if (res.status === 422 && data.errors) {
                            msg.innerHTML = `<div class="alert alert-danger">${Object.values(data.errors).flat().join('<br>')}</div>`;
                            return;
                        }
                        msg.innerHTML = `<div class="alert alert-danger">${data.message || 'Something went wrong'}</div>`;
                        return;
                    }

                    msg.innerHTML = `<div class="alert alert-success">${data.message || 'Returned successfully.'}</div>`;
                    setTimeout(() => window.location.href = "{{ route('hiring.agreements.show', $agreement) }}", 900);
                }).catch(() => {
                    msg.innerHTML = `<div class="alert alert-danger">Network error. Please try again.</div>`;
                });
            });
        });
    </script>
@endsection