{{--
    Payments for one job: totals, payment history and "Receive Payment" form.
    Needs: $job (with payments.receivedBy loaded)
    Used on the job view page and the delivery invoice page.
--}}
@php
    $payGrand = $job->grandTotal();
    $payPaid = (float)$job->payments->sum('amount');
    $payBalance = round($payGrand - $payPaid, 2);
@endphp

<div class="card info-card mb-3" id="paymentsCard">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <div>
                <h5 class="card-title mb-0">Payments</h5>
                <div class="muted-help">Advance and balance payments for this job</div>
            </div>
            @if ($payBalance > 0.009)
                <span class="badge bg-danger fs-6">Balance Due: {{ number_format($payBalance, 2) }}</span>
            @elseif ($payBalance < -0.009)
                <span class="badge bg-warning text-dark fs-6">Overpaid: {{ number_format(abs($payBalance), 2) }}</span>
            @else
                <span class="badge bg-success fs-6">Paid in Full</span>
            @endif
        </div>
    </div>

    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="row g-2 mb-3 text-center">
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <div class="text-muted small">Bill Total</div>
                            <div class="fw-bold fs-5">{{ number_format($payGrand, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <div class="text-muted small">Paid</div>
                            <div class="fw-bold fs-5 text-success">{{ number_format($payPaid, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <div class="text-muted small">Balance</div>
                            <div class="fw-bold fs-5 {{ $payBalance > 0.009 ? 'text-danger' : '' }}">
                                {{ number_format(max(0, $payBalance), 2) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Method</th>
                                <th class="text-end">Amount</th>
                                <th>Received By</th>
                                @can('payments.delete')
                                    <th style="width: 70px;"></th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($job->payments as $p)
                                <tr>
                                    <td>{{ $p->paid_at?->format('d M Y, h:i A') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $p->payment_type === 'advance' ? 'info' : ($p->payment_type === 'settlement' ? 'success' : 'secondary') }}">
                                            {{ $p->type_label }}
                                        </span>
                                        @if ($p->notes)
                                            <div class="text-muted small">{{ $p->notes }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $p->method_label }}</td>
                                    <td class="text-end fw-semibold">{{ number_format((float)$p->amount, 2) }}</td>
                                    <td>{{ $p->receivedBy?->name ?? '-' }}</td>
                                    @can('payments.delete')
                                        <td>
                                            <button type="button" class="btn btn-outline-danger btn-sm w-100 btnDeletePayment"
                                                data-url="{{ route('tailoring.payments.destroy', $p) }}">Delete</button>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No payments yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-5">
                @can('payments.create')
                    @if ($payBalance > 0.009)
                        <form id="paymentForm" class="border rounded p-3" action="{{ route('tailoring.payments.store', $job) }}">
                            <h6 class="mb-3">Receive Payment</h6>
                            <div id="paymentMsg"></div>

                            <div class="mb-2">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0.01" max="{{ $payBalance }}" name="amount"
                                        class="form-control" value="{{ $payBalance }}" required>
                                    <button type="button" class="btn btn-outline-secondary" id="btnFullBalance"
                                        data-balance="{{ $payBalance }}">Full balance</button>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label">Method</label>
                                    <select name="method" class="form-select">
                                        @foreach (\App\Models\JobPayment::METHODS as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="paid_at" class="form-control" value="{{ now()->toDateString() }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control" placeholder="Optional">
                            </div>

                            <button type="submit" class="btn btn-success w-100">Save Payment</button>
                        </form>
                    @else
                        <div class="alert alert-success mb-0">This bill is fully settled.</div>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const csrf = "{{ csrf_token() }}";

        document.getElementById('btnFullBalance')?.addEventListener('click', function() {
            document.querySelector('#paymentForm input[name="amount"]').value = this.dataset.balance;
        });

        document.getElementById('paymentForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const box = document.getElementById('paymentMsg');
            const btn = form.querySelector('button[type="submit"]');
            box.innerHTML = '';
            btn.disabled = true;

            fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                })
                .then(async res => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.success) {
                        const msg = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'Something went wrong');
                        box.innerHTML = `<div class="alert alert-danger py-2">${msg}</div>`;
                        btn.disabled = false;
                        return;
                    }
                    Swal.fire({ icon: 'success', title: data.message, timer: 1500, showConfirmButton: false })
                        .then(() => window.location.reload());
                })
                .catch(() => {
                    box.innerHTML = `<div class="alert alert-danger py-2">Something went wrong</div>`;
                    btn.disabled = false;
                });
        });

        document.querySelectorAll('.btnDeletePayment').forEach(btn => {
            btn.addEventListener('click', function() {
                const url = this.dataset.url;
                Swal.fire({
                    title: 'Delete this payment?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete'
                }).then(result => {
                    if (!result.isConfirmed) return;
                    fetch(url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) window.location.reload();
                            else Swal.fire('Error', data.message || 'Something went wrong', 'error');
                        })
                        .catch(() => Swal.fire('Error', 'Something went wrong', 'error'));
                });
            });
        });
    })();
</script>
