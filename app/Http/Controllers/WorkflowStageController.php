<?php

namespace App\Http\Controllers;

use App\Models\WorkflowStage;
use Illuminate\Http\Request;

class WorkflowStageController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));

        $stages = WorkflowStage::query()
            ->when($q, function ($query) use ($q) {
                $query->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%");
            })
            ->orderBy('sort_order')
            ->paginate(15)
            ->withQueryString();

        return view('workflow_stages.index', compact('stages', 'q'));
    }

    public function create()
    {
        return view('workflow_stages.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:workflow_stages,code'],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['sort_order'] = (int)($data['sort_order'] ?? 0);
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        $stage = WorkflowStage::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Workflow stage created',
                'data' => $stage,
            ]);
        }

        return redirect()->route('workflow-stages.index')->with('success', 'Workflow stage created');
    }

    public function edit(WorkflowStage $workflow_stage)
    {
        return view('workflow_stages.edit', ['stage' => $workflow_stage]);
    }

    public function update(Request $request, WorkflowStage $workflow_stage)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:workflow_stages,code,' . $workflow_stage->id],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['sort_order'] = (int)($data['sort_order'] ?? 0);
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        $workflow_stage->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Workflow stage updated',
            ]);
        }

        return redirect()->route('workflow-stages.index')->with('success', 'Workflow stage updated');
    }

    public function destroy(Request $request, WorkflowStage $workflow_stage)
    {
        $workflow_stage->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Workflow stage deleted',
            ]);
        }

        return redirect()->route('workflow-stages.index')->with('success', 'Workflow stage deleted');
    }
}