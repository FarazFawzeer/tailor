<?php

namespace App\Http\Controllers\Hiring;

use App\Http\Controllers\Controller;
use App\Models\HireAgreement;
use Illuminate\Http\Request;

class HiringSalesReportController extends Controller
{
    public function index(Request $request)
    {
        $from   = $request->get('from') ?: now()->startOfMonth()->toDateString();
        $to     = $request->get('to') ?: now()->toDateString();
        $status = (string) $request->get('status'); // optional: issued/returned/cancelled

        // Base query
        $q = HireAgreement::query()
            ->with(['customer'])
            ->whereDate('issue_date', '>=', $from)
            ->whereDate('issue_date', '<=', $to)
            ->when($status, fn($qq) => $qq->where('status', $status));

        // Summary KPIs
        $summary = (clone $q)->selectRaw("
                COUNT(*) as agreements,
                SUM(total_hire_amount) as hire_total,
                SUM(fine_amount) as fine_total,
                SUM(deposit_received) as deposit_total,
                SUM(amount_paid) as paid_total
            ")->first();

        $hireTotal    = (float)($summary->hire_total ?? 0);
        $fineTotal    = (float)($summary->fine_total ?? 0);
        $depositTotal = (float)($summary->deposit_total ?? 0);
        $paidTotal    = (float)($summary->paid_total ?? 0);

        $grandTotal   = $hireTotal + $fineTotal;
        $collected    = $depositTotal + $paidTotal;
        $pending      = max(0, $grandTotal - $collected);

        // Daily breakdown (chart/table)
        $daily = (clone $q)
            ->selectRaw("
                DATE(issue_date) as d,
                COUNT(*) as agreements,
                SUM(total_hire_amount) as hire_total,
                SUM(fine_amount) as fine_total,
                SUM(deposit_received) as deposit_total,
                SUM(amount_paid) as paid_total
            ")
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->map(function($r){
                $hire = (float)$r->hire_total;
                $fine = (float)$r->fine_total;
                $dep  = (float)$r->deposit_total;
                $paid = (float)$r->paid_total;

                $grand = $hire + $fine;
                $col   = $dep + $paid;
                $pend  = max(0, $grand - $col);

                return [
                    'date' => $r->d,
                    'agreements' => (int)$r->agreements,
                    'hire_total' => $hire,
                    'fine_total' => $fine,
                    'grand_total' => $grand,
                    'collected' => $col,
                    'pending' => $pend,
                ];
            });

        // Agreement list (optional detailed table)
        $agreements = (clone $q)
            ->latest('issue_date')
            ->paginate(20)
            ->withQueryString();

        return view('hiring.reports.sales', compact(
            'from','to','status',
            'summary','hireTotal','fineTotal','grandTotal','depositTotal','paidTotal','collected','pending',
            'daily','agreements'
        ));
    }
}