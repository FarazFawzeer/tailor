<?php

namespace App\Http\Controllers\Tailoring;

use App\Http\Controllers\Controller;
use App\Models\DressType;
use App\Models\Job;
use App\Models\JobBatch;
use App\Models\JobBatchItem;
use App\Models\MeasurementTemplate;
use Illuminate\Http\Request;

class JobBatchItemController extends Controller
{
    public function store(Request $request, Job $job, JobBatch $batch)
    {
        if ($batch->job_id !== $job->id) abort(404);

        $data = $request->validate([
            'dress_type_id' => ['required', 'exists:dress_types,id'],
            'measurement_template_id' => ['nullable', 'exists:measurement_templates,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'per_piece_measurement' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $item = JobBatchItem::create([
            'job_batch_id' => $batch->id,
            'dress_type_id' => $data['dress_type_id'],
            'measurement_template_id' => $data['measurement_template_id'] ?? null,
            'qty' => (int)$data['qty'],
            'per_piece_measurement' => (bool)($data['per_piece_measurement'] ?? false),
            'notes' => $data['notes'] ?? null,
        ]);

        $item->load(['dressType', 'measurementTemplate']);

        return response()->json([
            'success' => true,
            'message' => 'Item added to batch',
            'data' => $item
        ]);
    }

    public function destroy(Job $job, JobBatch $batch, JobBatchItem $item)
    {
        if ($batch->job_id !== $job->id) abort(404);
        if ($item->job_batch_id !== $batch->id) abort(404);

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed'
        ]);
    }
}