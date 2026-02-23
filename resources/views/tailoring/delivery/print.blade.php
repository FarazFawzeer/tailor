<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $job->job_no }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; color: #111; }
        .wrap { max-width: 900px; margin: 20px auto; }
        .row { display:flex; justify-content:space-between; gap:20px; }
        table { width:100%; border-collapse:collapse; margin-top:12px; }
        th, td { border:1px solid #ddd; padding:8px; }
        th { background:#f5f5f5; text-align:left; }
        .right { text-align:right; }
        .muted { color:#666; }
        @media print { .no-print { display:none; } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="no-print" style="margin-bottom:10px;">
        <button onclick="window.print()">Print</button>
    </div>

    <div class="row">
        <div>
            <h2 style="margin:0;">Invoice</h2>
            <div class="muted">Job No: <b>{{ $job->job_no }}</b></div>
            <div class="muted">Date: <b>{{ now()->format('d M Y') }}</b></div>
        </div>
        <div>
            <div class="muted">Customer:</div>
            <div><b>{{ $job->customer?->full_name ?? 'N/A' }}</b></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Batch</th>
                <th>Dress</th>
                <th class="right">Qty</th>
                <th class="right">Unit</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($job->batches as $b)
            @foreach($b->items as $it)
                <tr>
                    <td>{{ $b->batch_no }}</td>
                    <td>{{ $it->dressType?->name ?? 'N/A' }}</td>
                    <td class="right">{{ $it->qty }}</td>
                    <td class="right">{{ number_format((float)$it->unit_price, 2) }}</td>
                    <td class="right">{{ number_format((float)$it->line_total, 2) }}</td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>

    <table style="width: 360px; margin-left:auto;">
        <tr>
            <th>Sub Total</th>
            <td class="right">{{ number_format($subTotal, 2) }}</td>
        </tr>
        <tr>
            <th>Discount</th>
            <td class="right">{{ number_format($discount, 2) }}</td>
        </tr>
        <tr>
            <th>Grand Total</th>
            <td class="right"><b>{{ number_format($grandTotal, 2) }}</b></td>
        </tr>
    </table>

    @if($job->delivery)
        <p class="muted">
            Delivered Date: <b>{{ $job->delivery->delivered_date?->format('d M Y') }}</b> |
            Delivered By: <b>{{ $job->delivery->deliveredByUser?->name ?? 'N/A' }}</b>
        </p>
        @if($job->delivery->notes)
            <p class="muted">Notes: {{ $job->delivery->notes }}</p>
        @endif
    @endif

</div>
</body>
</html>