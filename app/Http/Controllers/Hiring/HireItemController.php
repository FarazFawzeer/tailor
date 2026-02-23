<?php

namespace App\Http\Controllers\Hiring;

use App\Http\Controllers\Controller;
use App\Models\HireItem;
use App\Models\HireItemImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HireItemController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));
        $status = (string)$request->get('status');

        $items = HireItem::query()
            ->with('images')
            ->when($q, fn($qq) => $qq->where('item_code', 'like', "%{$q}%")
                ->orWhere('name', 'like', "%{$q}%")
                ->orWhere('category', 'like', "%{$q}%"))
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('hiring.items.index', compact('items', 'q', 'status'));
    }

    public function create()
    {
        return view('hiring.items.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_code' => ['required','string','max:50','unique:hire_items,item_code'],
            'name' => ['required','string','max:255'],
            'category' => ['nullable','string','max:255'],
            'size' => ['nullable','string','max:50'],
            'color' => ['nullable','string','max:50'],
            'hire_price' => ['required','numeric','min:0'],
            'deposit_amount' => ['nullable','numeric','min:0'],
            'status' => ['required','in:available,reserved,hired,maintenance'],
            'notes' => ['nullable','string','max:2000'],
            'is_active' => ['nullable','boolean'],
            'images.*' => ['nullable','image','max:4096'],
        ]);

        $data['is_active'] = (bool)($data['is_active'] ?? true);

        $item = HireItem::create($data);

        // images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('hire-items', 'public');
                HireItemImage::create([
                    'hire_item_id' => $item->id,
                    'image_path' => 'storage/' . $path,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Hire item created successfully.']);
    }

    public function edit(HireItem $hire_item)
    {
        $hire_item->load('images');
        return view('hiring.items.edit', ['item' => $hire_item]);
    }

    public function update(Request $request, HireItem $hire_item)
    {
        $data = $request->validate([
            'item_code' => ['required','string','max:50','unique:hire_items,item_code,' . $hire_item->id],
            'name' => ['required','string','max:255'],
            'category' => ['nullable','string','max:255'],
            'size' => ['nullable','string','max:50'],
            'color' => ['nullable','string','max:50'],
            'hire_price' => ['required','numeric','min:0'],
            'deposit_amount' => ['nullable','numeric','min:0'],
            'status' => ['required','in:available,reserved,hired,maintenance'],
            'notes' => ['nullable','string','max:2000'],
            'is_active' => ['nullable','boolean'],
            'images.*' => ['nullable','image','max:4096'],
        ]);

        $data['is_active'] = (bool)($data['is_active'] ?? true);

        $hire_item->update($data);

        // add new images (does not delete old)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('hire-items', 'public');
                HireItemImage::create([
                    'hire_item_id' => $hire_item->id,
                    'image_path' => 'storage/' . $path,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Hire item updated successfully.']);
    }

    public function destroy(Request $request, HireItem $hire_item)
    {
        // delete images files
        foreach ($hire_item->images as $img) {
            $publicPath = str_replace('storage/', '', $img->image_path);
            Storage::disk('public')->delete($publicPath);
        }

        $hire_item->delete();

        return response()->json(['success' => true, 'message' => 'Hire item deleted successfully.']);
    }

    public function deleteImage(Request $request, HireItemImage $image)
    {
        $publicPath = str_replace('storage/', '', $image->image_path);
        Storage::disk('public')->delete($publicPath);
        $image->delete();

        return response()->json(['success' => true, 'message' => 'Image removed.']);
    }
}