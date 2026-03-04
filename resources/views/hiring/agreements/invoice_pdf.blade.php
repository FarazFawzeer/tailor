{{-- resources/views/hiring/agreements/invoice.blade.php --}}
@php
    $companyName  = config('app.name', 'Your Company');
    $companyPhone = config('app.company_phone', '');
    $companyAddr  = config('app.company_address', '');
    $currency     = 'Rs';

    $lines = $agreement->items ?? collect();

    $totalQty  = (int)$lines->sum(fn($l) => (int)($l->qty ?? 0));
    $subTotal  = (float)$lines->sum(fn($l) => (float)($l->line_total ?? ((float)($l->hire_price ?? 0) * (int)($l->qty ?? 0))));

    $depositReceived = (float)($agreement->deposit_received ?? 0);
    $paidSoFar       = (float)($agreement->amount_paid ?? 0);

    $finePerDay = (float)($agreement->fine_per_day ?? 0);
    $fineNow    = (float)($agreement->fine_amount ?? 0);

    $grandTotal = $subTotal + $fineNow;

    // ✅ Balance payable after deposit + any payments
    $balance = max(0, $grandTotal - $depositReceived - $paidSoFar);

    $issue = optional($agreement->issue_date)->format('d M Y');
    $due   = optional($agreement->expected_return_date)->format('d M Y');
    $status = ucfirst($agreement->status ?? 'issued');
@endphp

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $agreement->agreement_no }}</title>
    <style>
        * { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; }
        body { font-size: 12px; color:#111827; }
        .muted { color:#6b7280; }
        .title { font-size: 20px; font-weight: 800; letter-spacing:.5px; }
        .h1 { font-size: 16px; font-weight: 800; }
        .box { border:1px solid #e5e7eb; border-radius: 10px; padding:12px; }
        .hr { border-top:1px solid #e5e7eb; margin: 12px 0; }
        table { width:100%; border-collapse: collapse; }
        th { text-align:left; font-size:11px; color:#6b7280; padding:10px 8px; border-bottom:1px solid #e5e7eb; text-transform:uppercase; letter-spacing:.04em; }
        td { padding:10px 8px; border-bottom:1px solid #f1f5f9; vertical-align: top; }
        .text-right { text-align:right; }
        .bold { font-weight: 700; }
        .total-row td { border-bottom: none; }
        .bigTotal { font-size: 14px; font-weight: 900; }
        .badge { display:inline-block; padding:3px 8px; border:1px solid #e5e7eb; border-radius:999px; font-size:11px; color:#111827; background:#f9fafb; }
        .footer { font-size: 10px; color:#9ca3af; text-align:center; margin-top: 10px; }
        .kpi { border:1px solid #e5e7eb; border-radius:10px; padding:10px; }
        .kpi .l { font-size:10px; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; }
        .kpi .v { font-size:13px; font-weight:800; margin-top:4px; }
    </style>
</head>

<body>
    {{-- Header --}}
    <table>
        <tr>
            <td style="width:60%;">
                <div class="h1">{{ $companyName }}</div>
                <div class="muted" style="line-height:1.6; margin-top:6px;">
                    @if($companyAddr) {{ $companyAddr }} <br>@endif
                    @if($companyPhone) Phone: {{ $companyPhone }} @endif
                </div>
            </td>
            <td class="text-right" style="width:40%;">
                <div class="title">INVOICE</div>
                <div class="muted" style="margin-top:6px; line-height:1.6;">
                    Invoice No: <span class="bold">{{ $agreement->agreement_no }}</span><br>
                    Status: <span class="bold">{{ $status }}</span><br>
                    Issue Date: <span class="bold">{{ $issue ?? '-' }}</span><br>
                    Due (Return): <span class="bold">{{ $due ?? '-' }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="hr"></div>

    {{-- Bill To --}}
    <div class="box">
        <div class="muted" style="font-size:11px;">BILL TO</div>
        <div class="bold" style="font-size:14px; margin-top:4px;">
            {{ $agreement->customer?->full_name ?? 'N/A' }}
        </div>
        <div class="muted" style="line-height:1.6; margin-top:4px;">
            @if($agreement->customer?->phone) Phone: {{ $agreement->customer->phone }}<br>@endif
            @if($agreement->customer?->email) Email: {{ $agreement->customer->email }}<br>@endif
        </div>
    </div>

    <div class="hr"></div>

    {{-- Small KPIs --}}
    <table style="margin-bottom:12px;">
        <tr>
            <td style="width:33.33%; padding-right:8px;">
                <div class="kpi">
                    <div class="l">Total Qty</div>
                    <div class="v">{{ $totalQty }}</div>
                </div>
            </td>
            <td style="width:33.33%; padding:0 8px;">
                <div class="kpi">
                    <div class="l">Fine / Day</div>
                    <div class="v">{{ $currency }} {{ number_format($finePerDay,2) }}</div>
                </div>
            </td>
            <td style="width:33.33%; padding-left:8px;">
                <div class="kpi">
                    <div class="l">Balance Payable</div>
                    <div class="v">{{ $currency }} {{ number_format($balance,2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Items --}}
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Code</th>
                <th>Size</th>
                <th class="text-right">Price</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lines as $ai)
                @php
                    $price = (float)($ai->hire_price ?? 0);
                    $qty   = (int)($ai->qty ?? 0);
                    $lt    = (float)($ai->line_total ?? ($price * $qty));
                @endphp
                <tr>
                    <td>
                        <div class="bold">{{ $ai->item?->name ?? 'N/A' }}</div>
                        <div class="muted" style="font-size:11px;">{{ $ai->item?->category ?? '' }}</div>
                    </td>
                    <td class="bold">{{ $ai->item?->item_code ?? '-' }}</td>
                    <td><span class="badge">{{ $ai->size ?? '-' }}</span></td>
                    <td class="text-right">{{ $currency }} {{ number_format($price,2) }}</td>
                    <td class="text-right bold">{{ $qty }}</td>
                    <td class="text-right bold">{{ $currency }} {{ number_format($lt,2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div style="margin-top:14px;">
        <table>
            <tr>
                <td style="width:55%; vertical-align:top;">
                    <div class="box">
                        <div class="bold">Important</div>
                        <div class="muted" style="margin-top:6px; line-height:1.7;">
                            • Please return items on or before the expected return date to avoid fines.<br>
                            • Fine Per Day: <span class="bold">{{ $currency }} {{ number_format($finePerDay,2) }}</span><br>
                            • Total Qty: <span class="bold">{{ $totalQty }}</span>
                        </div>

                        @if($agreement->notes)
                            <div class="hr"></div>
                            <div class="muted"><span class="bold">Notes:</span> {{ $agreement->notes }}</div>
                        @endif
                    </div>
                </td>

                <td style="width:45%; vertical-align:top;">
                    <div class="box">
                        <table>
                            <tr>
                                <td class="muted">Subtotal</td>
                                <td class="text-right bold">{{ $currency }} {{ number_format($subTotal,2) }}</td>
                            </tr>
                            <tr>
                                <td class="muted">Fine</td>
                                <td class="text-right bold">{{ $currency }} {{ number_format($fineNow,2) }}</td>
                            </tr>
                            <tr>
                                <td class="muted">Grand Total</td>
                                <td class="text-right bold">{{ $currency }} {{ number_format($grandTotal,2) }}</td>
                            </tr>
                            <tr>
                                <td class="muted">Deposit Received</td>
                                <td class="text-right bold">- {{ $currency }} {{ number_format($depositReceived,2) }}</td>
                            </tr>
                            <tr>
                                <td class="muted">Paid So Far</td>
                                <td class="text-right bold">- {{ $currency }} {{ number_format($paidSoFar,2) }}</td>
                            </tr>
                            <tr class="total-row">
                                <td class="bold">Balance Payable</td>
                                <td class="text-right bigTotal">{{ $currency }} {{ number_format($balance,2) }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        This is a system generated invoice. Thank you.
    </div>
</body>
</html>