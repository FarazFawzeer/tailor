<?php

namespace App\Http\Controllers\Tailoring;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Job;
use App\Models\WorkflowStage;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));

        $jobs = Job::query()
            ->with(['customer', 'currentStage'])
            ->when($q, function ($query) use ($q) {
                $query->where('job_no', 'like', "%{$q}%")
                    ->orWhereHas('customer', fn($c) => $c->where('full_name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('tailoring.jobs.index', compact('jobs', 'q'));
    }

    public function create()
    {
        $customers = Customer::latest()->take(200)->get(); // simple list for now
        $firstStage = WorkflowStage::where('is_active', true)->orderBy('sort_order')->first();

        return view('tailoring.jobs.create', compact('customers', 'firstStage'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'job_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $firstStage = WorkflowStage::where('is_active', true)->orderBy('sort_order')->first();

        $job = Job::create([
            'customer_id' => $data['customer_id'],
            'job_date' => $data['job_date'] ?? now()->toDateString(),
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'current_stage_id' => $firstStage?->id,
            'created_by' => auth()->id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Job created ({$job->job_no})",
                'data' => $job
            ]);
        }

        return redirect()->route('tailoring.jobs.index')->with('success', 'Job created');
    }

    public function show(Job $job)
    {
        $job->load(['customer', 'currentStage', 'batches.items.dressType', 'batches.items.measurementTemplate']);
        return view('tailoring.jobs.show', compact('job'));
    }
}