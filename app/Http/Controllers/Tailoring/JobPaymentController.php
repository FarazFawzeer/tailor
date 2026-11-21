<?php

namespace App\Http\Controllers\Tailoring;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobPayment;
use App\Models\WorkflowStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JobPaymentController extends Controller
{
    /** Pending bills: jobs where the customer still owes money */
    public function pending(Request $request)
    {
        $q = trim((string)$request->get('q'));
        $filter = (string)$request->get('filter'); // '' | ready | overdue
        $deliveredStageId = WorkflowStage::where('code', 'DELIVERED')->value('id');

        // job is "ready" when every item is completed or has reached the Delivering stage
        $notReadySql = '(SELECT COUNT(*) FROM job_batch_items jbi JOIN job_batches jb ON jb.id = jbi.job_batch_id
            WHERE jb.job_id = jobs.id AND jbi.completed_at IS NULL AND (jbi.current_stage_id IS NULL OR jbi.current_stage_id <> ?))';

        $base = Job::query()
            ->pendingBills()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('job_no', 'like', "%{$q}%")
                        ->orWhereHas('customer', fn($c) => $c->where(function ($cc) use ($q) {
                            $cc->where('full_name', 'like', "%{$q}%")
                                ->orWhere('phone', 'like', "%{$q}%");
                        }));
                });
            });

        // summary over all pending bills (before the ready/overdue filter)
        $summary = [
            'count' => (clone $base)->count(),
            'outstanding' => (float)(clone $base)->sum(DB::raw(Job::sqlBalance())),
            'ready' => (clone $base)->whereRaw("{$notReadySql} = 0", [(int)$deliveredStageId])->count(),
            'overdue' => (clone $base)->whereDate('due_date', '<', today())->count(),
        ];

        $jobs = (clone $base)
            ->withBillTotals()
            ->selectRaw("{$notReadySql} AS not_ready_items", [(int)$deliveredStageId])
            ->with(['customer', 'currentStage', 'payments'])
            ->when($filter === 'ready', fn($query) => $query->whereRaw("{$notReadySql} = 0", [(int)$deliveredStageId]))
            ->when($filter === 'overdue', fn($query) => $query->whereDate('due_date', '<', today()))
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->paginate(15)
            ->withQueryString();

        return view('tailoring.payments.pending', compact('jobs', 'summary', 'q', 'filter'));
    }

    public function store(Request $request, Job $job)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', Rule::in(array_keys(JobPayment::METHODS))],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $balance = $job->balance();
        $amount = round((float)$data['amount'], 2);

        if ($balance <= 0) {
            return response()->json(['success' => false, 'message' => 'This bill is already fully paid.'], 422);
        }

        if ($amount > $balance + 0.009) {
            return response()->json([
                'success' => false,
                'message' => 'Amount is more than the balance (' . number_format($balance, 2) . ').',
            ], 422);
        }

        JobPayment::create([
            'job_id' => $job->id,
            'amount' => $amount,
            // paying the full remaining balance = final settlement
            'payment_type' => $amount >= $balance - 0.009 ? 'settlement' : 'payment',
            'method' => $data['method'],
            'paid_at' => !empty($data['paid_at']) ? $data['paid_at'] . ' ' . now()->format('H:i:s') : now(),
            'received_by' => auth()->id(),
            'notes' => $data['notes'] ?? null,
        ]);

        $newBalance = $job->balance();

        return response()->json([
            'success' => true,
            'message' => $newBalance <= 0 ? 'Payment saved. Bill is fully settled.' : 'Payment saved. Balance: ' . number_format($newBalance, 2),
            'balance' => $newBalance,
        ]);
    }

    public function destroy(JobPayment $payment)
    {
        $payment->delete();

        return response()->json(['success' => true, 'message' => 'Payment deleted.']);
    }
}
