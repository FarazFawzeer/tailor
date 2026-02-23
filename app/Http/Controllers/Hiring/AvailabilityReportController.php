<?php

namespace App\Http\Controllers\Hiring;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AvailabilityReportController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $next7 = Carbon::today()->addDays(7);

        // ===== Summary counts =====
        $totalItems = DB::table('hire_items')->count();

        $availableCount = DB::table('hire_items')
            ->where('status', 'available')
            ->count();

        $hiredCount = DB::table('hire_items')
            ->where('status', 'hired')
            ->count();

        // Optional statuses (only if you have them)
        $maintenanceCount = DB::table('hire_items')
            ->whereIn('status', ['maintenance'])
            ->count();

        $damagedCount = DB::table('hire_items')
            ->whereIn('status', ['damaged'])
            ->count();

        // ===== Overdue agreements =====
        // Rule: agreement is overdue if expected_return_date < today AND has at least one item still not available
        // (we use hire_items.status = hired to detect still out)
        $overdueAgreements = DB::table('hire_agreements as ha')
            ->join('hire_agreement_items as hai', 'hai.hire_agreement_id', '=', 'ha.id')
            ->join('hire_items as hi', 'hi.id', '=', 'hai.hire_item_id')
            ->whereDate('ha.expected_return_date', '<', $today->toDateString())
            ->where('hi.status', '=', 'hired')
            ->select('ha.id', 'ha.agreement_no', 'ha.customer_id', 'ha.issue_date', 'ha.expected_return_date')
            ->distinct()
            ->orderBy('ha.expected_return_date')
            ->limit(10)
            ->get();

        $overdueCount = DB::table('hire_agreements as ha')
            ->join('hire_agreement_items as hai', 'hai.hire_agreement_id', '=', 'ha.id')
            ->join('hire_items as hi', 'hi.id', '=', 'hai.hire_item_id')
            ->whereDate('ha.expected_return_date', '<', $today->toDateString())
            ->where('hi.status', '=', 'hired')
            ->distinct('ha.id')
            ->count('ha.id');

        // ===== Upcoming returns (next 7 days) =====
        $upcomingReturns = DB::table('hire_agreements as ha')
            ->join('customers as c', 'c.id', '=', 'ha.customer_id')
            ->join('hire_agreement_items as hai', 'hai.hire_agreement_id', '=', 'ha.id')
            ->join('hire_items as hi', 'hi.id', '=', 'hai.hire_item_id')
            ->whereBetween(DB::raw('date(ha.expected_return_date)'), [$today->toDateString(), $next7->toDateString()])
            ->where('hi.status', '=', 'hired')
            ->select(
                'ha.id',
                'ha.agreement_no',
                'ha.expected_return_date',
                'c.full_name',
                DB::raw('COUNT(hi.id) as items_out')
            )
            ->groupBy('ha.id', 'ha.agreement_no', 'ha.expected_return_date', 'c.full_name')
            ->orderBy('ha.expected_return_date')
            ->limit(10)
            ->get();

        // ===== Availability by category =====
        // If you don't have category column, change to dress_type or name group
        $categoryStats = DB::table('hire_items')
            ->select(
                DB::raw("COALESCE(category,'Uncategorized') as category"),
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN status='available' THEN 1 ELSE 0 END) as available"),
                DB::raw("SUM(CASE WHEN status='hired' THEN 1 ELSE 0 END) as hired")
            )
            ->groupBy(DB::raw("COALESCE(category,'Uncategorized')"))
            ->orderBy('category')
            ->get()
            ->map(function ($row) {
                $row->utilization = $row->total > 0
                    ? round(($row->hired / $row->total) * 100, 1)
                    : 0;
                return $row;
            });

        return view('hiring.availability.index', compact(
            'totalItems',
            'availableCount',
            'hiredCount',
            'maintenanceCount',
            'damagedCount',
            'overdueCount',
            'overdueAgreements',
            'upcomingReturns',
            'categoryStats'
        ));
    }

    // Full list overdue (optional page)
    public function overdue()
    {
        $today = Carbon::today();

        $items = DB::table('hire_agreements as ha')
            ->join('customers as c', 'c.id', '=', 'ha.customer_id')
            ->join('hire_agreement_items as hai', 'hai.hire_agreement_id', '=', 'ha.id')
            ->join('hire_items as hi', 'hi.id', '=', 'hai.hire_item_id')
            ->whereDate('ha.expected_return_date', '<', $today->toDateString())
            ->where('hi.status', '=', 'hired')
            ->select(
                'ha.agreement_no',
                'ha.issue_date',
                'ha.expected_return_date',
                'c.full_name',
                DB::raw('COUNT(hi.id) as items_out')
            )
            ->groupBy('ha.agreement_no', 'ha.issue_date', 'ha.expected_return_date', 'c.full_name')
            ->orderBy('ha.expected_return_date')
            ->paginate(20);

        return view('hiring.availability.overdue', compact('items'));
    }

    // Full list upcoming returns (optional page)
    public function upcomingReturns()
    {
        $today = Carbon::today();
        $next7 = Carbon::today()->addDays(7);

        $items = DB::table('hire_agreements as ha')
            ->join('customers as c', 'c.id', '=', 'ha.customer_id')
            ->join('hire_agreement_items as hai', 'hai.hire_agreement_id', '=', 'ha.id')
            ->join('hire_items as hi', 'hi.id', '=', 'hai.hire_item_id')
            ->whereBetween(DB::raw('date(ha.expected_return_date)'), [$today->toDateString(), $next7->toDateString()])
            ->where('hi.status', '=', 'hired')
            ->select(
                'ha.agreement_no',
                'ha.expected_return_date',
                'c.full_name',
                DB::raw('COUNT(hi.id) as items_out')
            )
            ->groupBy('ha.agreement_no', 'ha.expected_return_date', 'c.full_name')
            ->orderBy('ha.expected_return_date')
            ->paginate(20);

        return view('hiring.availability.upcoming', compact('items'));
    }
}