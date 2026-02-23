@extends('layouts.vertical', ['subtitle' => 'Invoice Summary'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Delivery', 'subtitle' => 'Invoice Summary'])

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Invoice - Job {{ $job->job_no }}</h5>
            <p class="card-subtitle mb-0">
                Customer: <b>{{ $job->customer?->full_name ?? 'N/A' }}</b>
                @if($job->delivery)
                    | <span class="badge bg-success">Delivered</span>
                @else
                    | <span class="badge bg-secondary">Not Delivered</span>
                @endif
            </p>
        </div>

        <div class="card-body">
            <div id="message"></div>

            {{-- Items --}}
            <div class="table-responsive mb-3">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Batch</th>
                            <th>Dress</th>
                            <th>Qty</th>
                            <th style="width:160px;">Unit Price</th>
                            <th>Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($job->batches as $b)
                            @foreach($b->items as $it)
                                <tr>
                                    <td>{{ $b->batch_no }}</td>
                                    <td>{{ $it->dressType?->name ?? 'N/A' }}</td>
                                    <td>{{ $it->qty }}</td>
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                            class="form-control form-control-sm unit-price"
                                            name="unit_price[{{ $it->id }}]"
                                            value="{{ (float)$it->unit_price }}">
                                    </td>
                                    <td>{{ number_format((float)$it->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-outline-dark" id="btnSavePrices">Save Prices</button>
            </div>

            {{-- Totals --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="card border">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Sub Total</span>
                                <b>{{ number_format($subTotal, 2) }}</b>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Discount</span>
                                <b>{{ number_format($discount, 2) }}</b>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span>Grand Total</span>
                                <b>{{ number_format($grandTotal, 2) }}</b>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Delivery --}}
                <div class="col-md-6">
                    <div class="card border">
                        <div class="card-header">
                            <b>Delivery</b>
                        </div>
                        <div class="card-body">
                            <form id="deliverForm">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Delivered Date</label>
                                    <input type="date" class="form-control" name="delivered_date"
                                        value="{{ $job->delivery?->delivered_date?->format('Y-m-d') ?? now()->format('Y-m-d') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Discount</label>
                                    <input type="number" step="0.01" min="0" class="form-control" name="discount"
                                        value="{{ (float)($job->delivery?->discount ?? 0) }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" name="notes" rows="2">{{ $job->delivery?->notes }}</textarea>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('tailoring.delivery.index') }}" class="btn btn-secondary">Back</a>
                                    <button type="submit" class="btn btn-success">Mark Delivered</button>
                                    <a href="{{ route('tailoring.delivery.print', $job) }}" target="_blank" class="btn btn-info">Print</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        const msgBox = document.getElementById('message');

        // Save prices
        document.getElementById('btnSavePrices').addEventListener('click', function() {
            const formData = new FormData();
            document.querySelectorAll('.unit-price').forEach(inp => {
                formData.append(inp.name, inp.value);
            });

            fetch("{{ route('tailoring.delivery.prices', $job) }}", {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            }).then(async res => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    msgBox.innerHTML = `<div class="alert alert-danger">${data.message || 'Error saving prices'}</div>`;
                    return;
                }
                msgBox.innerHTML = `<div class="alert alert-success">${data.message} (Refresh to recalculate totals)</div>`;
                setTimeout(() => msgBox.innerHTML = "", 2500);
            });
        });

        // Mark delivered
        document.getElementById('deliverForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch("{{ route('tailoring.delivery.deliver', $job) }}", {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            }).then(async res => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    msgBox.innerHTML = `<div class="alert alert-danger">${data.message || 'Error'}</div>`;
                    return;
                }
                msgBox.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                setTimeout(() => window.location.reload(), 800);
            });
        });
    </script>
@endsection