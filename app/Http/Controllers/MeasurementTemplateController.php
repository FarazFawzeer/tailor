<?php

namespace App\Http\Controllers;

use App\Models\DressType;
use App\Models\MeasurementTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

            // fields (array)
            'fields' => ['nullable', 'array'],
            'fields.*.label' => ['required_with:fields', 'string', 'max:255'],
            'fields.*.key' => ['nullable', 'string', 'max:255'],
            'fields.*.unit' => ['required_with:fields', 'in:inch,cm'],
            'fields.*.input_type' => ['required_with:fields', 'in:number,text'],
            'fields.*.sort_order' => ['nullable', 'integer'],
            'fields.*.is_required' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool)($data['is_active'] ?? true);

        return DB::transaction(function () use ($request, $data) {

            $template = MeasurementTemplate::create([
                'dress_type_id' => $data['dress_type_id'],
                'name' => $data['name'],
                'notes' => $data['notes'] ?? null,
                'is_active' => $data['is_active'],
            ]);

            $fields = $data['fields'] ?? [];

            // build rows for createMany
            $rows = [];
            foreach ($fields as $f) {
                $label = trim((string)($f['label'] ?? ''));
                if ($label === '') continue;

                $key = trim((string)($f['key'] ?? ''));
                if ($key === '') {
                    $key = Str::slug($label, '_'); // auto key if empty
                }

                $rows[] = [
                    'label' => $label,
                    'key' => $key,
                    'unit' => $f['unit'],
                    'input_type' => $f['input_type'],
                    'sort_order' => (int)($f['sort_order'] ?? 0),
                    'is_required' => (bool)($f['is_required'] ?? false),
                ];
            }

            if (count($rows) > 0) {
                $template->fields()->createMany($rows);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Measurement template created (with fields)',
                    'data' => $template->load('fields')
                ]);
            }

            return redirect()->route('measurement-templates.index')->with('success', 'Template created');
        });
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

    public function fieldsJson(MeasurementTemplate $template)
{
    $template->load(['fields' => fn($q) => $q->orderBy('sort_order')]);

    return response()->json([
        'success' => true,
        'data' => $template->fields->map(function ($f) {
            return [
                'id' => $f->id,
                'label' => $f->label,
                'key' => $f->key,
                'unit' => $f->unit,
                'input_type' => $f->input_type,
                'is_required' => (bool)$f->is_required,
            ];
        }),
    ]);
}
}
