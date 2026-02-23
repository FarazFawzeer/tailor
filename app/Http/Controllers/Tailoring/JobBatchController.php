<?php

namespace App\Http\Controllers\Tailoring;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobBatch;
use Illuminate\Http\Request;

class JobBatchController extends Controller
{
    public function store(Request $request, Job $job)
    {
        $data = $request->validate([
            'batch_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $batchNo = JobBatch::nextBatchNo($job->id);

        $batch = JobBatch::create([
            'job_id' => $job->id,
            'batch_no' => $batchNo,
            'batch_date' => $data['batch_date'] ?? now()->toDateString(),
            'due_date' => $data['due_date'] ?? $job->due_date,
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Batch created ({$batch->batch_no})",
            'data' => $batch
        ]);
    }

    public function destroy(Job $job, JobBatch $batch)
    {
        if ($batch->job_id !== $job->id) abort(404);

        $batch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Batch deleted'
        ]);
    }
}