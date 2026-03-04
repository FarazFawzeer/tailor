<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoiceNo }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .wrap { padding: 20px 24px; }
        .row { width: 100%; }
        .muted { color: #666; }
        .title { font-size: 20px; font-weight: 700; margin: 0; }
        .small { font-size: 11px; }
        .hr { height: 1px; background: #eee; margin: 14px 0; }

        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e6e6e6; padding: 8px; vertical-align: top; }
        th { background: #f5f5f5; font-weight: 700; text-align: left; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .no-border td, .no-border th { border: none; }

        .totals td { border: none; padding: 4px 0; }
        .totals .label { text-align: right; color: #555; }
        .totals .val { text-align: right; font-weight: 700; }

        .badge { display:inline-block; padding: 3px 8px; border-radius: 999px; background:#f2f2f2; font-size: 11px; }
        .footer { margin-top: 26px; }
        .signature { margin-top: 28px; }
        .signature .line { width: 220px; height: 1px; background: #333; margin-top: 22px; }
    </style>
</head>
<body>
<div class="wrap">

    {{-- Header --}}
    <table class="no-border">
        <tr>
            <td style="width: 60%;">
                <p class="title">{{ $company['name'] }}</p>
                <div class="muted small">
                    {{ $company['address'] }}<br>
                    Phone: {{ $company['phone'] }}<br>
                    Email: {{ $company['email'] }}
                </div>
            </td>
            <td class="text-right" style="width: 40%;">
                <p class="title" style="margin-bottom:6px;">INVOICE</p>
                <div class="small">
                    <b>Invoice No:</b> {{ $invoiceNo }}<br>
                    <b>Invoice Date:</b> {{ \Carbon\Carbon::parse($invoiceDate)->format('d M Y') }}<br>
                    <b>Job No:</b> {{ $job->job_no }}<br>
                    <span class="badge">Stage: {{ $job->currentStage?->name ?? '-' }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="hr"></div>

    {{-- Bill To / Job Details --}}
    <table class="no-border">
        <tr>
            <td style="width: 55%;">
                <b>Bill To</b><br>
                <div style="margin-top:6px;">
                    <b>{{ $job->customer?->full_name ?? '-' }}</b><br>
                    <span class="muted">Phone:</span> {{ $job->customer?->phone ?? '-' }}<br>
                    @if(!empty($job->customer?->email))
                        <span class="muted">Email:</span> {{ $job->customer?->email }}<br>
                    @endif
                    @if(!empty($job->customer?->address))
                        <span class="muted">Address:</span> {{ $job->customer?->address }}<br>
                    @endif
                </div>
            </td>

            <td style="width: 45%;">
                <b>Job Details</b><br>
                <div style="margin-top:6px;">
                    <span class="muted">Job Date:</span> {{ $job->job_date?->format('d M Y') ?? '-' }}<br>
                    <span class="muted">Due Date:</span> {{ $job->due_date?->format('d M Y') ?? '-' }}<br>
                    <span class="muted">Notes:</span> {{ $job->notes ?? '-' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="hr"></div>

    {{-- Items Table --}}
    <table>
        <thead>
            <tr>
                <th style="width: 70px;">Batch</th>
                <th>Dress</th>
                <th style="width: 140px;">Template</th>
                <th class="text-center" style="width: 60px;">Qty</th>
                <th class="text-right" style="width: 90px;">Unit Price</th>
                <th class="text-right" style="width: 100px;">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lines as $ln)
                <tr>
                    <td>{{ $ln['batch_no'] }}</td>
                    <td>
                        <b>{{ $ln['dress'] }}</b>
                        @if(!empty($ln['notes']))
                            <div class="muted small">Note: {{ $ln['notes'] }}</div>
                        @endif
                    </td>
                    <td>{{ $ln['template'] }}</td>
                    <td class="text-center">{{ $ln['qty'] }}</td>
                    <td class="text-right">{{ number_format((float)$ln['unit_price'], 2) }}</td>
                    <td class="text-right"><b>{{ number_format((float)$ln['line_total'], 2) }}</b></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center muted">No items.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Totals --}}
    <table class="no-border" style="margin-top: 14px;">
        <tr>
            <td style="width: 55%;">
                <div class="small muted">
                    <b>Terms:</b> Please check measurements before final stitching.<br>
                    <b>Payment:</b> Pay on delivery / as agreed.
                </div>
            </td>
            <td style="width: 45%;">
                <table class="totals" style="width:100%;">
                    <tr>
                        <td class="label">Sub Total</td>
                        <td class="val">{{ number_format((float)$subTotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Discount</td>
                        <td class="val">{{ number_format((float)$discount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="font-size: 13px;"><b>Grand Total</b></td>
                        <td class="val" style="font-size: 13px;"><b>{{ number_format((float)$grandTotal, 2) }}</b></td>
                    </tr>
                    <tr>
                        <td class="label muted">Currency</td>
                        <td class="val muted">LKR</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer">
        <div class="signature">
            <div class="line"></div>
            <div class="small muted">Authorized Signature</div>
        </div>

        <div class="small muted" style="margin-top: 12px;">
            This is a system-generated invoice.
        </div>
    </div>

</div>
</body>
</html>