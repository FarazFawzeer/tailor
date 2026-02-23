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
                $query->where('job_no', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($qq) use ($q) {
                        $qq->where('full_name', 'like', "%{$q}%");
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
        $discount = (float)($job->delivery?->discount ?? 0);
        $grandTotal = max(0, $subTotal - $discount);

        return view('tailoring.delivery.invoice', compact('job', 'items', 'subTotal', 'discount', 'grandTotal'));
    }

    public function updatePrices(Request $request, Job $job)
    {
        // update unit prices quickly from invoice screen
        $data = $request->validate([
            'unit_price' => ['required', 'array'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['unit_price'] as $itemId => $price) {
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
        $discount = (float)($data['discount'] ?? 0);
        $grandTotal = max(0, $subTotal - $discount);

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
        $discount = (float)($job->delivery?->discount ?? 0);
        $grandTotal = max(0, $subTotal - $discount);

        return view('tailoring.delivery.print', compact('job', 'items', 'subTotal', 'discount', 'grandTotal'));
    }
}