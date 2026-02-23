<?php

namespace App\Http\Controllers\Hiring;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\HireAgreement;
use App\Models\HireAgreementItem;
use App\Models\HireItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HireAgreementController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));
        $status = (string)$request->get('status');

        $agreements = HireAgreement::query()
            ->with(['customer'])
            ->when($q, function ($qq) use ($q) {
                $qq->where('agreement_no', 'like', "%{$q}%")
                    ->orWhereHas('customer', fn($c) => $c->where('full_name', 'like', "%{$q}%"));
            })
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('hiring.agreements.index', compact('agreements', 'q', 'status'));
    }

    public function create()
    {
        $customers = Customer::query()->orderBy('full_name')->get(['id', 'full_name', 'phone']);
        $availableItems = HireItem::query()
            ->with('images')
            ->where('is_active', 1)
            ->where('status', 'available')
            ->orderBy('item_code')
            ->get();

        $agreementNo = HireAgreement::nextAgreementNo();

        return view('hiring.agreements.create', compact('customers', 'availableItems', 'agreementNo'));
    }

    // AJAX helper: add item by code
    public function findItemByCode(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        $item = HireItem::query()
            ->with('images')
            ->where('item_code', $data['code'])
            ->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item code not found.'], 404);
        }

        if ($item->status !== 'available') {
            return response()->json(['success' => false, 'message' => 'Item is not available (currently ' . $item->status . ').'], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $item->id,
                'item_code' => $item->item_code,
                'name' => $item->name,
                'hire_price' => (float)$item->hire_price,
                'deposit_amount' => (float)$item->deposit_amount,
                'thumb' => $item->images->first()?->image_path ? asset($item->images->first()->image_path) : asset('/images/users/avatar-6.jpg'),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'agreement_no' => ['required','string','max:50','unique:hire_agreements,agreement_no'],
            'customer_id' => ['required','exists:customers,id'],
            'issue_date' => ['required','date'],
            'expected_return_date' => ['required','date','after_or_equal:issue_date'],
            'fine_per_day' => ['nullable','numeric','min:0'],
            'deposit_received' => ['nullable','numeric','min:0'],
            'notes' => ['nullable','string','max:2000'],
            'item_ids' => ['required','array','min:1'],
            'item_ids.*' => ['required','integer','exists:hire_items,id'],
        ]);

        DB::transaction(function () use ($data) {

            // lock selected items and re-check availability
            $items = HireItem::whereIn('id', $data['item_ids'])->lockForUpdate()->get();

            foreach ($items as $it) {
                if ($it->status !== 'available') {
                    throw new \RuntimeException("Item {$it->item_code} is not available.");
                }
            }

            $agreement = HireAgreement::create([
                'agreement_no' => $data['agreement_no'],
                'customer_id' => (int)$data['customer_id'],
                'issue_date' => $data['issue_date'],
                'expected_return_date' => $data['expected_return_date'],
                'fine_per_day' => (float)($data['fine_per_day'] ?? 0),
                'deposit_received' => (float)($data['deposit_received'] ?? 0),
                'notes' => $data['notes'] ?? null,
                'status' => 'issued',
                'created_by' => auth()->id(),
            ]);

            $totalHire = 0;

            foreach ($items as $it) {
                HireAgreementItem::create([
                    'hire_agreement_id' => $agreement->id,
                    'hire_item_id' => $it->id,
                    'hire_price' => (float)$it->hire_price,
                    'deposit_amount' => (float)$it->deposit_amount,
                ]);

                $totalHire += (float)$it->hire_price;

                // mark item hired
                $it->update(['status' => 'hired']);
            }

            $agreement->update([
                'total_hire_amount' => $totalHire,
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Hire agreement created and items issued.']);
    }

    public function show(HireAgreement $hire_agreement)
    {
        $hire_agreement->load(['customer', 'items.item.images', 'createdBy', 'returnedBy']);
        return view('hiring.agreements.show', ['agreement' => $hire_agreement]);
    }

    public function returnForm(HireAgreement $hire_agreement)
    {
        $hire_agreement->load(['customer', 'items.item.images']);

        if ($hire_agreement->status !== 'issued') {
            return redirect()->route('hiring.agreements.show', $hire_agreement)
                ->with('error', 'Only issued agreements can be returned.');
        }

        return view('hiring.agreements.return', ['agreement' => $hire_agreement]);
    }

    public function returnStore(Request $request, HireAgreement $hire_agreement)
    {
        if ($hire_agreement->status !== 'issued') {
            return response()->json(['success' => false, 'message' => 'Agreement is not in issued status.'], 422);
        }

        $data = $request->validate([
            'actual_return_date' => ['required','date'],
            'notes' => ['nullable','string','max:2000'],
        ]);

        DB::transaction(function () use ($hire_agreement, $data) {

            $hire_agreement->load('items.item');

            $expected = $hire_agreement->expected_return_date;
            $actual = \Carbon\Carbon::parse($data['actual_return_date']);

            $lateDays = 0;
            if ($actual->greaterThan($expected)) {
                $lateDays = $expected->diffInDays($actual);
            }

            $fine = $lateDays * (float)$hire_agreement->fine_per_day;

            // mark all items available again
            foreach ($hire_agreement->items as $ai) {
                $ai->item->update(['status' => 'available']);
            }

            $hire_agreement->update([
                'actual_return_date' => $actual->toDateString(),
                'fine_amount' => $fine,
                'status' => 'returned',
                'returned_by' => auth()->id(),
                'notes' => $data['notes'] ?? $hire_agreement->notes,
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Items returned successfully and fine calculated.']);
    }
}