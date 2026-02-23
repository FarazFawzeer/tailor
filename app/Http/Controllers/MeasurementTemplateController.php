<?php

namespace App\Http\Controllers;

use App\Models\DressType;
use App\Models\MeasurementTemplate;
use Illuminate\Http\Request;

class MeasurementTemplateController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));

        $templates = MeasurementTemplate::query()
            ->with('dressType')
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhereHas('dressType', fn($dt) => $dt->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('measurement_templates.index', compact('templates', 'q'));
    }

    public function create()
    {
        $dressTypes = DressType::where('is_active', true)->orderBy('name')->get();
        return view('measurement_templates.create', compact('dressTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'dress_type_id' => ['required', 'exists:dress_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool)($data['is_active'] ?? true);

        $template = MeasurementTemplate::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Measurement template created',
                'data' => $template
            ]);
        }

        return redirect()->route('measurement-templates.index')->with('success', 'Template created');
    }

    public function edit(MeasurementTemplate $measurement_template)
    {
        $measurement_template->load(['dressType', 'fields']);
        $dressTypes = DressType::where('is_active', true)->orderBy('name')->get();

        return view('measurement_templates.edit', [
            'template' => $measurement_template,
            'dressTypes' => $dressTypes,
        ]);
    }

    public function update(Request $request, MeasurementTemplate $measurement_template)
    {
        $data = $request->validate([
            'dress_type_id' => ['required', 'exists:dress_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool)($data['is_active'] ?? true);

        $measurement_template->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Measurement template updated']);
        }

        return redirect()->route('measurement-templates.index')->with('success', 'Template updated');
    }

    public function destroy(Request $request, MeasurementTemplate $measurement_template)
    {
        $measurement_template->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Template deleted']);
        }

        return redirect()->route('measurement-templates.index')->with('success', 'Template deleted');
    }
}