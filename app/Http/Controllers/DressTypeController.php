<?php

namespace App\Http\Controllers;

use App\Models\DressType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DressTypeController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));

        $dressTypes = DressType::query()
            ->when($q, function ($query) use ($q) {
                $query->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('dress_types.index', compact('dressTypes', 'q'));
    }

    public function create()
    {
        return view('dress_types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:dress_types,code'],
            'name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],

            // ✅ image validation
            'diagram_front' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'diagram_back'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        // ✅ store images
        if ($request->hasFile('diagram_front')) {
            $path = $request->file('diagram_front')->store('dress-diagrams', 'public');
            $data['diagram_front'] = 'storage/' . $path;
        }

        if ($request->hasFile('diagram_back')) {
            $path = $request->file('diagram_back')->store('dress-diagrams', 'public');
            $data['diagram_back'] = 'storage/' . $path;
        }

        $dressType = DressType::create($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Dress type created', 'data' => $dressType]);
        }

        return redirect()->route('dress-types.index')->with('success', 'Dress type created');
    }

    public function edit(DressType $dress_type)
    {
        return view('dress_types.edit', ['dressType' => $dress_type]);
    }

    public function update(Request $request, DressType $dress_type)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:dress_types,code,' . $dress_type->id],
            'name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],

            // ✅ image validation
            'diagram_front' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'diagram_back'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],

            // ✅ optional remove checkboxes
            'remove_diagram_front' => ['nullable', 'boolean'],
            'remove_diagram_back'  => ['nullable', 'boolean'],
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        // ✅ remove front if requested
        if (!empty($data['remove_diagram_front'])) {
            $this->deletePublicFileIfExists($dress_type->diagram_front);
            $data['diagram_front'] = null;
        } else {
            unset($data['diagram_front']); // avoid overriding by validation null
        }

        // ✅ remove back if requested
        if (!empty($data['remove_diagram_back'])) {
            $this->deletePublicFileIfExists($dress_type->diagram_back);
            $data['diagram_back'] = null;
        } else {
            unset($data['diagram_back']);
        }

        // ✅ replace front if new upload
        if ($request->hasFile('diagram_front')) {
            $this->deletePublicFileIfExists($dress_type->diagram_front);

            $path = $request->file('diagram_front')->store('dress-diagrams', 'public');
            $data['diagram_front'] = 'storage/' . $path;
        }

        // ✅ replace back if new upload
        if ($request->hasFile('diagram_back')) {
            $this->deletePublicFileIfExists($dress_type->diagram_back);

            $path = $request->file('diagram_back')->store('dress-diagrams', 'public');
            $data['diagram_back'] = 'storage/' . $path;
        }

        $dress_type->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Dress type updated']);
        }

        return redirect()->route('dress-types.index')->with('success', 'Dress type updated');
    }

    public function destroy(Request $request, DressType $dress_type)
    {
        // ✅ delete stored images too
        $this->deletePublicFileIfExists($dress_type->diagram_front);
        $this->deletePublicFileIfExists($dress_type->diagram_back);

        $dress_type->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Dress type deleted']);
        }

        return redirect()->route('dress-types.index')->with('success', 'Dress type deleted');
    }

    private function deletePublicFileIfExists(?string $publicPath): void
    {
        if (!$publicPath) return;

        // stored as "storage/dress-diagrams/xxx.png"
        if (str_starts_with($publicPath, 'storage/')) {
            $relative = substr($publicPath, strlen('storage/')); // "dress-diagrams/xxx.png"
            Storage::disk('public')->delete($relative);
        }
    }
}