<?php

namespace App\Http\Controllers\Hiring;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\HireAgreement;
use App\Models\HireAgreementItem;
use App\Models\HireItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\HireItemVariant;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

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
            ->with(['images', 'variants']) // ✅ must load variants
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
            'agreement_no' => ['required', 'string', 'max:50', 'unique:hire_agreements,agreement_no'],
            'customer_id' => ['required', 'exists:customers,id'],
            'issue_date' => ['required', 'date'],
            'expected_return_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'fine_per_day' => ['nullable', 'numeric', 'min:0'],
            'deposit_received' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],

            // ✅ NEW: lines structure
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.hire_item_id' => ['required', 'integer', 'exists:hire_items,id'],
            'lines.*.size' => ['required', 'string', 'max:50'],
            'lines.*.qty' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($data) {

            // Create agreement first
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

            foreach ($data['lines'] as $line) {

                $itemId = (int)$line['hire_item_id'];
                $size   = trim((string)$line['size']);
                $qty    = (int)$line['qty'];

                // Lock variant row
                $variant = HireItemVariant::query()
                    ->where('hire_item_id', $itemId)
                    ->where('size', $size)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    throw ValidationException::withMessages([
                        'lines' => ["Variant not found for item #{$itemId} size {$size}."],
                    ]);
                }

                if ((int)$variant->qty < $qty) {
                    throw ValidationException::withMessages([
                        'lines' => ["Not enough stock for {$size}. Available: {$variant->qty}, Requested: {$qty}."],
                    ]);
                }

                // Lock item
                $item = HireItem::query()->where('id', $itemId)->lockForUpdate()->first();

                if (!$item || $item->status !== 'available') {
                    throw ValidationException::withMessages([
                        'lines' => ["Item is not available (ID: {$itemId})."],
                    ]);
                }

                // Save agreement line (you should add columns size + qty to hire_agreement_items)
                HireAgreementItem::create([
                    'hire_agreement_id' => $agreement->id,
                    'hire_item_id' => $item->id,
                    'size' => $size,
                    'qty' => $qty,
                    'hire_price' => (float)$item->hire_price,
                    'deposit_amount' => (float)($item->deposit_amount ?? 0),
                    'line_total' => (float)$item->hire_price * $qty,
                ]);

                // Deduct stock
                $variant->update([
                    'qty' => (int)$variant->qty - $qty,
                ]);

                $totalHire += ((float)$item->hire_price * $qty);

                // If this item has zero stock now -> mark hired (optional rule)
                $remaining = (int)HireItemVariant::where('hire_item_id', $item->id)->sum('qty');
                if ($remaining <= 0) {
                    $item->update(['status' => 'hired']);
                }
            }

            $agreement->update([
                'total_hire_amount' => $totalHire,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Hire agreement created and items issued.',
            'invoice_url' => route('hiring.agreements.invoice', $agreement->id)
        ]);
    }

    public function edit(HireAgreement $hire_agreement)
    {
        // Only allow edit if not returned (you can adjust this rule)
        if ($hire_agreement->status === 'returned') {
            return redirect()
                ->route('hiring.agreements.show', $hire_agreement)
                ->with('error', 'Returned agreements cannot be edited.');
        }

        $hire_agreement->load([
            'customer',
            'items.item.images',
            'items.item.variants'
        ]);

        $customers = Customer::query()->orderBy('full_name')->get(['id', 'full_name', 'phone']);

        /**
         * Important:
         * When editing, we must show:
         * 1) all currently available items
         * 2) PLUS items already used in this agreement (even if they are currently "hired")
         */
        $lockedItemIds = $hire_agreement->items->pluck('hire_item_id')->unique()->values()->all();

        $availableItems = HireItem::query()
            ->with(['images', 'variants'])
            ->where('is_active', 1)
            ->where(function ($q) use ($lockedItemIds) {
                $q->where('status', 'available')
                    ->orWhereIn('id', $lockedItemIds);
            })
            ->orderBy('item_code')
            ->get();

        return view('hiring.agreements.edit', [
            'agreement' => $hire_agreement,
            'customers' => $customers,
            'availableItems' => $availableItems,
        ]);
    }

    public function update(Request $request, HireAgreement $hire_agreement)
    {
        if ($hire_agreement->status === 'returned') {
            return response()->json(['success' => false, 'message' => 'Returned agreements cannot be edited.'], 422);
        }

        $data = $request->validate([
            'agreement_no' => ['required', 'string', 'max:50', 'unique:hire_agreements,agreement_no,' . $hire_agreement->id],
            'customer_id' => ['required', 'exists:customers,id'],
            'issue_date' => ['required', 'date'],
            'expected_return_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'fine_per_day' => ['nullable', 'numeric', 'min:0'],
            'deposit_received' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],

            // NEW lines
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.hire_item_id' => ['required', 'integer', 'exists:hire_items,id'],
            'lines.*.size' => ['required', 'string', 'max:50'],
            'lines.*.qty' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($hire_agreement, $data) {

            // Load current agreement items
            $hire_agreement->load(['items', 'items.item']);

            /**
             * STEP 1: Restore previous stock back to variants
             * (Because we are going to re-apply the NEW lines after)
             */
            foreach ($hire_agreement->items as $oldLine) {
                $oldItemId = (int)$oldLine->hire_item_id;
                $oldSize   = (string)($oldLine->size ?? '');
                $oldQty    = (int)($oldLine->qty ?? 0);

                if (!$oldItemId || !$oldSize || $oldQty <= 0) continue;

                $v = HireItemVariant::query()
                    ->where('hire_item_id', $oldItemId)
                    ->where('size', $oldSize)
                    ->lockForUpdate()
                    ->first();

                // If variant exists, restore qty
                if ($v) {
                    $v->update(['qty' => (int)$v->qty + $oldQty]);
                }
            }

            /**
             * STEP 2: Remove old agreement lines
             */
            HireAgreementItem::where('hire_agreement_id', $hire_agreement->id)->delete();

            /**
             * STEP 3: Update agreement header details
             */
            $hire_agreement->update([
                'agreement_no' => $data['agreement_no'],
                'customer_id' => (int)$data['customer_id'],
                'issue_date' => $data['issue_date'],
                'expected_return_date' => $data['expected_return_date'],
                'fine_per_day' => (float)($data['fine_per_day'] ?? 0),
                'deposit_received' => (float)($data['deposit_received'] ?? 0),
                'notes' => $data['notes'] ?? null,
                // keep status as issued (you can change if you want)
            ]);

            /**
             * STEP 4: Apply NEW lines (deduct stock again)
             */
            $totalHire = 0;
            $touchedItemIds = [];

            foreach ($data['lines'] as $line) {

                $itemId = (int)$line['hire_item_id'];
                $size   = trim((string)$line['size']);
                $qty    = (int)$line['qty'];

                // Lock variant row
                $variant = HireItemVariant::query()
                    ->where('hire_item_id', $itemId)
                    ->where('size', $size)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    throw ValidationException::withMessages([
                        'lines' => ["Variant not found for item #{$itemId} size {$size}."],
                    ]);
                }

                if ((int)$variant->qty < $qty) {
                    throw ValidationException::withMessages([
                        'lines' => ["Not enough stock for size {$size}. Available: {$variant->qty}, Requested: {$qty}."],
                    ]);
                }

                // Lock item row
                $item = HireItem::query()->where('id', $itemId)->lockForUpdate()->first();
                if (!$item) {
                    throw ValidationException::withMessages([
                        'lines' => ["Item not found (ID: {$itemId})."],
                    ]);
                }

                // Create new agreement line
                HireAgreementItem::create([
                    'hire_agreement_id' => $hire_agreement->id,
                    'hire_item_id' => $item->id,
                    'size' => $size,
                    'qty' => $qty,
                    'hire_price' => (float)$item->hire_price,
                    'deposit_amount' => (float)($item->deposit_amount ?? 0),
                    'line_total' => (float)$item->hire_price * $qty,
                ]);

                // Deduct stock
                $variant->update(['qty' => (int)$variant->qty - $qty]);

                $totalHire += ((float)$item->hire_price * $qty);
                $touchedItemIds[] = $item->id;
            }

            /**
             * STEP 5: Recalculate item statuses for affected items
             */
            $touchedItemIds = array_values(array_unique($touchedItemIds));

            foreach ($touchedItemIds as $id) {
                $remaining = (int) HireItemVariant::where('hire_item_id', $id)->sum('qty');
                HireItem::where('id', $id)->update([
                    'status' => $remaining > 0 ? 'available' : 'hired'
                ]);
            }

            /**
             * IMPORTANT:
             * Some items were in the OLD agreement but removed in NEW agreement.
             * They got stock restored in step1, but we must also set their status properly.
             */
            $oldItemIds = $hire_agreement->items->pluck('hire_item_id')->unique()->values()->all();
            $checkAlso = array_diff($oldItemIds, $touchedItemIds);

            foreach ($checkAlso as $id) {
                $remaining = (int) HireItemVariant::where('hire_item_id', $id)->sum('qty');
                HireItem::where('id', $id)->update([
                    'status' => $remaining > 0 ? 'available' : 'hired'
                ]);
            }

            /**
             * STEP 6: Update agreement totals
             */
            $hire_agreement->update([
                'total_hire_amount' => $totalHire,
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Hire agreement updated successfully.']);
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
            'actual_return_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],

            // payment (required if pending exists)
            'pay_now' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:30'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($hire_agreement, $data) {

            $hire_agreement->load(['items.item']);

            $subTotal = (float) $hire_agreement->items->sum('line_total');
            $deposit  = (float) ($hire_agreement->deposit_received ?? 0);
            $paid     = (float) ($hire_agreement->amount_paid ?? 0); // ✅ add this column in hire_agreements
            $payNow   = (float) ($data['pay_now'] ?? 0);

            // Calculate fine
            $expected = $hire_agreement->expected_return_date;
            $actual = \Carbon\Carbon::parse($data['actual_return_date']);

            $lateDays = 0;
            if ($actual->greaterThan($expected)) {
                $lateDays = $expected->diffInDays($actual);
            }

            $fine = $lateDays * (float) $hire_agreement->fine_per_day;

            // Pending at return time (including fine)
            $grand   = $subTotal + $fine;
            $pending = max(0, $grand - $deposit - $paid);

            // ✅ Block return if pending not fully paid
            if ($pending > 0 && $payNow < $pending) {
                throw ValidationException::withMessages([
                    'pay_now' => ["Payment required. Pending Rs " . number_format($pending, 2) . " must be fully paid to complete return."],
                ]);
            }

            // Restore stock to each variant
            foreach ($hire_agreement->items as $line) {

                $v = HireItemVariant::where('hire_item_id', $line->hire_item_id)
                    ->where('size', $line->size)
                    ->lockForUpdate()
                    ->first();

                if ($v) {
                    $v->update(['qty' => (int)$v->qty + (int)$line->qty]);
                }
            }

            // Update item status based on remaining stock
            $affectedItemIds = $hire_agreement->items->pluck('hire_item_id')->unique()->values()->all();

            foreach ($affectedItemIds as $id) {
                $remaining = (int) HireItemVariant::where('hire_item_id', $id)->sum('qty');
                HireItem::where('id', $id)->update([
                    'status' => $remaining > 0 ? 'available' : 'hired'
                ]);
            }

            $hire_agreement->update([
                'actual_return_date' => $actual->toDateString(),
                'fine_amount' => $fine,
                'status' => 'returned',
                'returned_by' => auth()->id(),
                'notes' => $data['notes'] ?? $hire_agreement->notes,

                // ✅ Payment accumulation
                'amount_paid' => $paid + $payNow,
                'payment_method' => $data['payment_method'] ?? $hire_agreement->payment_method,
                'payment_reference' => $data['payment_reference'] ?? $hire_agreement->payment_reference,
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Items returned successfully. Fine calculated and payment recorded.']);
    }
    public function destroy(HireAgreement $hire_agreement)
    {
        if ($hire_agreement->status === 'returned') {
            return response()->json(['success' => false, 'message' => 'Returned agreements cannot be deleted.'], 422);
        }

        // Optional: restore stock before delete
        DB::transaction(function () use ($hire_agreement) {
            $hire_agreement->load('items');

            foreach ($hire_agreement->items as $line) {
                $v = HireItemVariant::where('hire_item_id', $line->hire_item_id)
                    ->where('size', $line->size)
                    ->lockForUpdate()
                    ->first();

                if ($v) $v->update(['qty' => (int)$v->qty + (int)$line->qty]);
            }

            HireAgreementItem::where('hire_agreement_id', $hire_agreement->id)->delete();
            $hire_agreement->delete();
        });

        return response()->json(['success' => true, 'message' => 'Agreement deleted successfully.']);
    }

    public function invoice(HireAgreement $hire_agreement)
    {
        $hire_agreement->load([
            'customer',
            'items.item', // includes item details
        ]);

        $pdf = Pdf::loadView('hiring.agreements.invoice_pdf', [
            'agreement' => $hire_agreement
        ])->setPaper('A4', 'portrait');

        $fileName = 'Hire-Invoice-' . $hire_agreement->agreement_no . '.pdf';

        // download
        return $pdf->download($fileName);

        // OR if you want open in browser:
        // return $pdf->stream($fileName);
    }
}
