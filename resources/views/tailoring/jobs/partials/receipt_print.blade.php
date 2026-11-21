{{--
    80mm thermal receipt printing, shared by the job create / edit / view pages:
      printMeasurementReceipt(payload) - one measurement slip per item
      printJobBill(bill)               - customer bill (shape from JobController::buildBillPayload)
--}}
<script>
    (function() {
        const COMPANY_NAME = @json(config('app.name', 'Tailoring Shop'));

        function escapeHtml(str) {
            return String(str ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function formatMoney(n) {
            const x = Number(n || 0);
            return x.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

                function printMeasurementReceipt(payload) {
                    const piecesHtml = (payload.pieces || []).map(piece => {
                        const rows = (piece.fields || []).map(f => `
                            <tr>
                                <td class="f-label">${escapeHtml(f.label)}${f.unit ? ` <span class="f-unit">(${escapeHtml(f.unit)})</span>` : ''}</td>
                                <td class="f-value">${f.value !== null && f.value !== '' && f.value !== undefined ? escapeHtml(String(f.value)) : '-'}</td>
                            </tr>
                        `).join('');

                        return `
                            <div class="piece-block">
                                <div class="piece-title">${escapeHtml(piece.title)}</div>
                                <table class="meas-table">
                                    ${rows}
                                </table>
                                ${piece.notes ? `<div class="piece-notes">Note: ${escapeHtml(piece.notes)}</div>` : ''}
                            </div>
                        `;
                    }).join('<div class="divider-dashed"></div>');

                    const now = new Date();
                    const printedAt = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();

                    const html = `
        <!DOCTYPE html>
        <html>
        <head>
        <meta charset="utf-8">
        <title>Measurement Receipt</title>
        <style>
            @page { size: 80mm auto; margin: 0; }
            * { box-sizing: border-box; }
            body {
                width: 80mm;
                margin: 0 auto;
                padding: 4mm 4mm 8mm 4mm;
                font-family: Arial, Helvetica, sans-serif; /* prints darker than Courier on thermal printers */
                font-size: 14px;
                font-weight: 700;
                color: #000;
                background: #fff;
            }
            .center { text-align: center; }
            .bold { font-weight: 700; }
            .shop-name { font-size: 19px; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 2px; }
            .shop-sub { font-size: 12px; color: #000; margin-bottom: 6px; }
            .divider-solid { border-top: 1px solid #000; margin: 6px 0; }
            .divider-dashed { border-top: 1px dashed #000; margin: 8px 0; }
            .meta-table { width: 100%; font-size: 14px; margin-bottom: 4px; }
            .meta-table td { padding: 1px 0; vertical-align: top; }
            .meta-label { width: 38%; font-weight: 800; white-space: nowrap; }
            .meta-value { width: 62%; }
            .section-title {
                font-size: 15px; font-weight: 800; text-transform: uppercase;
                letter-spacing: 0.5px; margin: 6px 0 4px 0; text-align: center;
                background: #000; color: #fff; padding: 3px 0;
            }
            .piece-block { margin-bottom: 4px; }
            .piece-title { font-weight: 800; font-size: 15px; margin-bottom: 3px; text-decoration: underline; }
            .meas-table { width: 100%; border-collapse: collapse; font-size: 15px; }
            .meas-table tr { border-bottom: 1px dotted #000; }
            .meas-table td { padding: 4px 0; }
            .f-label { width: 62%; font-weight: 700; }
            .f-unit { font-size: 12px; color: #000; }
            .f-value { width: 38%; text-align: right; font-weight: 900; font-size: 18px; }
            .piece-notes { font-size: 13px; font-style: italic; margin-top: 2px; color: #000; }
            .item-notes-box { font-size: 13px; margin-top: 6px; padding: 4px; border: 1px dashed #000; }
            .footer { margin-top: 10px; font-size: 11px; color: #000; text-align: center; }
            .qty-badge { display: inline-block; border: 1px solid #000; padding: 1px 8px; border-radius: 10px; font-size: 14px; font-weight: 800; }
        </style>
        </head>
        <body>

            <div class="center shop-name">${escapeHtml(COMPANY_NAME)}</div>
            <div class="center shop-sub">Measurement Slip</div>

            <div class="divider-solid"></div>

            <table class="meta-table">
                <tr>
                    <td class="meta-label">Job No</td>
                    <td class="meta-value bold">${escapeHtml(payload.job_no || '-')}</td>
                </tr>
                <tr>
                    <td class="meta-label">Batch No</td>
                    <td class="meta-value">${escapeHtml(payload.batch_no || '-')}</td>
                </tr>
                <tr>
                    <td class="meta-label">Dress</td>
                    <td class="meta-value bold">${escapeHtml(payload.dress_name || '-')}</td>
                </tr>
                <tr>
                    <td class="meta-label">Template</td>
                    <td class="meta-value">${escapeHtml(payload.template_name || '-')}</td>
                </tr>
                <tr>
                    <td class="meta-label">Qty</td>
                    <td class="meta-value"><span class="qty-badge">${escapeHtml(String(payload.qty ?? '-'))}</span></td>
                </tr>
            </table>

            <div class="section-title">Measurements</div>

            ${piecesHtml || '<div class="center" style="padding:10px 0;">No measurement data</div>'}

            ${payload.item_notes ? `<div class="item-notes-box"><b>Item Notes:</b> ${escapeHtml(payload.item_notes)}</div>` : ''}

            <div class="divider-dashed"></div>

            <div class="footer">
                Printed: ${escapeHtml(printedAt)}
            </div>

        </body>
        </html>
                    `;

                    const printWin = window.open('', '_blank', 'width=400,height=600');
                    if (!printWin) {
                        alert('Please allow popups to print the receipt.');
                        return;
                    }

                    printWin.document.open();
                    printWin.document.write(html);
                    printWin.document.close();

                    printWin.onload = function() {
                        printWin.focus();
                        printWin.print();
                    };
                }

                function printJobBill(bill) {
                    // bill shape (matches JobController::buildBillPayload()):
                    // {
                    //   invoice_no, invoice_date, job_no,
                    //   customer_name, customer_phone,
                    //   lines: [{ batch_no, dress, qty, unit_price, line_total }, ...],
                    //   sub_total, discount, grand_total,
                    //   paid_total, balance, payments: [{ date, type, method, amount }]
                    // }

                    const hasPayments = bill.paid_total !== undefined && bill.paid_total !== null;
                    const balance = Number(bill.balance || 0);

                    const paymentRowsHtml = (bill.payments || []).map(p => `
                        <tr>
                            <td class="t-label">${escapeHtml(p.type_label || 'Paid')} (${escapeHtml(p.date || '')})</td>
                            <td class="t-value">${formatMoney(p.amount)}</td>
                        </tr>
                    `).join('');

                    const rowsHtml = (bill.lines || []).map(ln => `
                        <tr>
                            <td class="b-batch">${escapeHtml(ln.batch_no || '-')}</td>
                            <td class="b-dress">${escapeHtml(ln.dress || '-')}</td>
                            <td class="b-qty">${escapeHtml(String(ln.qty ?? 0))}</td>
                            <td class="b-price">${formatMoney(ln.unit_price)}</td>
                            <td class="b-total">${formatMoney(ln.line_total)}</td>
                        </tr>
                    `).join('');

                    const html = `
        <!DOCTYPE html>
        <html>
        <head>
        <meta charset="utf-8">
        <title>Bill ${escapeHtml(bill.invoice_no || '')}</title>
        <style>
            @page { size: 80mm auto; margin: 0; }
            * { box-sizing: border-box; }
            body {
                width: 80mm;
                margin: 0 auto;
                padding: 4mm 4mm 8mm 4mm;
                font-family: 'Courier New', Consolas, monospace;
                font-size: 12px;
                color: #000;
                background: #fff;
            }
            .center { text-align: center; }
            .bold { font-weight: 700; }
            .shop-name { font-size: 16px; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 2px; }
            .shop-sub { font-size: 10px; color: #333; margin-bottom: 6px; }
            .divider-solid { border-top: 1px solid #000; margin: 6px 0; }
            .divider-dashed { border-top: 1px dashed #000; margin: 8px 0; }
            .meta-table { width: 100%; font-size: 11.5px; margin-bottom: 4px; }
            .meta-table td { padding: 1px 0; vertical-align: top; }
            .meta-label { width: 40%; font-weight: 700; white-space: nowrap; }
            .meta-value { width: 60%; }
            .items-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 6px; }
            .items-table th {
                border-bottom: 1px solid #000;
                text-align: left;
                padding: 3px 2px;
                font-size: 10.5px;
            }
            .items-table td {
                border-bottom: 1px dotted #999;
                padding: 4px 2px;
                vertical-align: top;
            }
            .b-qty { text-align: center; width: 10%; }
            .b-price, .b-total { text-align: right; width: 22%; }
            .b-dress { width: 36%; }
            .b-batch { width: 20%; font-size: 10px; color: #444; }
            .totals-table { width: 100%; margin-top: 6px; font-size: 12px; }
            .totals-table td { padding: 2px 0; }
            .totals-table .t-label { text-align: right; color: #333; width: 60%; }
            .totals-table .t-value { text-align: right; font-weight: 700; width: 40%; }
            .grand-row .t-label, .grand-row .t-value { font-size: 14px; font-weight: 800; }
            .footer { margin-top: 12px; font-size: 9.5px; color: #333; text-align: center; }
            .pay-status { margin-top: 6px; text-align: center; font-size: 13px; font-weight: 800; border: 1px solid #000; padding: 4px; }
            .thanks { margin-top: 8px; text-align: center; font-size: 12px; font-weight: 700; }
        </style>
        </head>
        <body>

            <div class="center shop-name">${escapeHtml(COMPANY_NAME)}</div>
            <div class="center shop-sub">Customer Bill / Receipt</div>

            <div class="divider-solid"></div>

            <table class="meta-table">
                <tr>
                    <td class="meta-label">Invoice No</td>
                    <td class="meta-value bold">${escapeHtml(bill.invoice_no || '-')}</td>
                </tr>
                <tr>
                    <td class="meta-label">Date</td>
                    <td class="meta-value">${escapeHtml(bill.invoice_date || '-')}</td>
                </tr>
                <tr>
                    <td class="meta-label">Job No</td>
                    <td class="meta-value bold">${escapeHtml(bill.job_no || '-')}</td>
                </tr>
                <tr>
                    <td class="meta-label">Customer</td>
                    <td class="meta-value">${escapeHtml(bill.customer_name || '-')}</td>
                </tr>
                <tr>
                    <td class="meta-label">Phone</td>
                    <td class="meta-value">${escapeHtml(bill.customer_phone || '-')}</td>
                </tr>
            </table>

            <div class="divider-dashed"></div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th class="b-batch">Batch</th>
                        <th class="b-dress">Dress</th>
                        <th class="b-qty">Qty</th>
                        <th class="b-price">Price</th>
                        <th class="b-total">Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${rowsHtml || '<tr><td colspan="5" class="center" style="padding:8px 0;">No items</td></tr>'}
                </tbody>
            </table>

            <table class="totals-table">
                <tr>
                    <td class="t-label">Sub Total</td>
                    <td class="t-value">${formatMoney(bill.sub_total)}</td>
                </tr>
                <tr>
                    <td class="t-label">Discount</td>
                    <td class="t-value">${formatMoney(bill.discount)}</td>
                </tr>
                <tr class="grand-row">
                    <td class="t-label">Grand Total</td>
                    <td class="t-value">${formatMoney(bill.grand_total)}</td>
                </tr>
                ${hasPayments ? `
                ${paymentRowsHtml}
                <tr>
                    <td class="t-label">Total Paid</td>
                    <td class="t-value">${formatMoney(bill.paid_total)}</td>
                </tr>
                <tr class="grand-row">
                    <td class="t-label">${balance > 0 ? 'Balance Due' : 'Balance'}</td>
                    <td class="t-value">${formatMoney(Math.max(0, balance))}</td>
                </tr>` : ''}
            </table>

            ${hasPayments ? `<div class="pay-status">${balance > 0 ? 'BALANCE TO PAY: ' + formatMoney(balance) : 'PAID IN FULL'}</div>` : ''}

            <div class="divider-dashed"></div>

            <div class="thanks">Thank You!</div>

            <div class="footer">
                Printed: ${escapeHtml(new Date().toLocaleString())}
            </div>

        </body>
        </html>
                    `;

                    const printWin = window.open('', '_blank', 'width=400,height=600');
                    if (!printWin) {
                        alert('Please allow popups to print the bill.');
                        return;
                    }

                    printWin.document.open();
                    printWin.document.write(html);
                    printWin.document.close();

                    printWin.onload = function () {
                        printWin.focus();
                        printWin.print();
                    };
                }

        window.printMeasurementReceipt = printMeasurementReceipt;
        window.printJobBill = printJobBill;
    })();
</script>
