<?php

namespace App\Http\Controllers;

use App\Models\MeasurementField;
use App\Models\MeasurementTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MeasurementFieldController extends Controller
{
    public function store(Request $request, MeasurementTemplate $template)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'key' => ['nullable', 'string', 'max:255'],
            'unit' => ['required', 'in:inch,cm'],
            'input_type' => ['required', 'in:number,text'],
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $key = trim((string)($data['key'] ?? ''));
        $data['key'] = $key !== '' ? Str::snake($key) : Str::snake($data['label']);
        $data['is_required'] = (bool)($data['is_required'] ?? false);
        $data['sort_order'] = (int)($data['sort_order'] ?? 0);
        $data['measurement_template_id'] = $template->id;

        $field = MeasurementField::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Field added',
            'data' => $field
        ]);
    }

    public function update(Request $request, MeasurementTemplate $template, MeasurementField $field)
    {
        if ($field->measurement_template_id !== $template->id) {
            abort(404);
        }

        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'in:inch,cm'],
            'input_type' => ['required', 'in:number,text'],
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['key'] = Str::snake($data['key']);
        $data['is_required'] = (bool)($data['is_required'] ?? false);
        $data['sort_order'] = (int)($data['sort_order'] ?? 0);

        $field->update($data);

        return response()->json(['success' => true, 'message' => 'Field updated']);
    }

    public function destroy(MeasurementTemplate $template, MeasurementField $field)
    {
        if ($field->measurement_template_id !== $template->id) {
            abort(404);
        }

        $field->delete();

        return response()->json(['success' => true, 'message' => 'Field deleted']);
    }
}