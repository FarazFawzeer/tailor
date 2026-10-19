<?php

namespace App\Http\Controllers\Tailoring;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));

        $jobs = Job::query()
            ->with(['customer', 'delivery'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('job_no', 'like', "%{$q}%")
                        ->orWhereHas('customer', function ($qq) use ($q) {
                            $qq->where('full_name', 'like', "%{$q}%");
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('tailoring.delivery.index', compact('jobs', 'q'));
    }

    public function invoice(Job $job)
    {
        $job->load([
            'customer',
            'delivery',
            'batches.items.dressType',
        ]);

        // calculate totals from items
        $items = collect();
        foreach ($job->batches as $b) {
            foreach ($b->items as $it) {
                $items->push($it);
            }
        }

        $subTotal = (float)$items->sum('line_total');
        $discount = $job->effectiveDiscount();
        $grandTotal = max(0, $subTotal - $discount);

        return view('tailoring.delivery.invoice', compact('job', 'items', 'subTotal', 'discount', 'grandTotal'));
    }

    public function updatePrices(Request $request, Job $job)
    {
        // update unit prices quickly from invoice screen
        $data = $request->validate([
            'unit_price' => ['required', 'array'],
        ]);

        $jobItemIds = DB::table('job_batch_items')
            ->join('job_batches', 'job_batches.id', '=', 'job_batch_items.job_batch_id')
            ->where('job_batches.job_id', $job->id)
            ->pluck('job_batch_items.id')
            ->all();

        DB::transaction(function () use ($data, $jobItemIds) {
            foreach ($data['unit_price'] as $itemId => $price) {
                if (!in_array((int)$itemId, $jobItemIds, true)) {
                    continue; // ignore items that don't belong to this job
                }

                DB::table('job_batch_items')
                    ->where('id', $itemId)
                    ->update([
                        'unit_price' => (float)$price,
                        'line_total' => DB::raw("qty * " . ((float)$price)),
                        'updated_at' => now(),
                    ]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Prices updated']);
    }

    public function deliver(Request $request, Job $job)
    {
        $data = $request->validate([
            'delivered_date' => ['required', 'date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $job->load(['batches.items']);

        // totals
        $items = $job->batches->flatMap(fn($b) => $b->items);
        $subTotal = (float)$items->sum('line_total');
        $discount = (float)($data['discount'] ?? $job->discount ?? 0);
        $grandTotal = max(0, $subTotal - $discount);

        // keep job discount in sync so bill/invoice/delivery all agree
        $job->update(['discount' => $discount]);

        Delivery::updateOrCreate(
            ['job_id' => $job->id],
            [
                'delivered_date' => $data['delivered_date'],
                'delivered_by' => auth()->id(),
                'sub_total' => $subTotal,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'notes' => $data['notes'] ?? null,
            ]
        );

        return response()->json(['success' => true, 'message' => 'Marked as Delivered', 'grand_total' => $grandTotal]);
    }

    public function print(Job $job)
    {
        $job->load(['customer', 'delivery', 'batches.items.dressType']);

        $items = $job->batches->flatMap(fn($b) => $b->items);

        $subTotal = (float)$items->sum('line_total');
        $discount = $job->effectiveDiscount();
        $grandTotal = max(0, $subTotal - $discount);

        return view('tailoring.delivery.print', compact('job', 'items', 'subTotal', 'discount', 'grandTotal'));
    }
}