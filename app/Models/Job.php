<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Job extends Model
{
    protected $fillable = [
        'job_no',
        'customer_id',
        'job_date',
        'due_date',
        'notes',
        'discount',
        'current_stage_id',
        'created_by',
    ];

    protected $casts = [
        'job_date' => 'date',
        'due_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function batches()
    {
        return $this->hasMany(JobBatch::class)->latest();
    }

    public function currentStage()
    {
        return $this->belongsTo(WorkflowStage::class, 'current_stage_id');
    }

    protected static function booted()
    {
        // Temporary unique value, then derive the real number from the auto-increment id
        // (max(id)+1 could collide when two jobs are saved at the same time)
        static::creating(function ($job) {
            if (empty($job->job_no)) {
                $job->job_no = 'TMP-' . uniqid('', true);
            }
        });

        static::created(function ($job) {
            if (str_starts_with((string)$job->job_no, 'TMP-')) {
                $job->job_no = 'JOB-' . str_pad((string)$job->id, 6, '0', STR_PAD_LEFT);
                $job->saveQuietly();
            }
        });
    }


    public function delivery()
    {
        return $this->hasOne(\App\Models\Delivery::class, 'job_id');
    }

    public function payments()
    {
        return $this->hasMany(JobPayment::class)->orderBy('paid_at')->orderBy('id');
    }

    // ---- Billing (raw SQL so lists can sort/filter by balance) ----

    public const SQL_ITEMS_TOTAL = '(SELECT COALESCE(SUM(COALESCE(jbi.line_total, jbi.qty * jbi.unit_price)), 0)
        FROM job_batch_items jbi JOIN job_batches jb ON jb.id = jbi.job_batch_id WHERE jb.job_id = jobs.id)';

    public const SQL_PAID_TOTAL = '(SELECT COALESCE(SUM(jp.amount), 0) FROM job_payments jp WHERE jp.job_id = jobs.id)';

    public const SQL_DISCOUNT = 'COALESCE((SELECT d.discount FROM deliveries d WHERE d.job_id = jobs.id ORDER BY d.id DESC LIMIT 1), jobs.discount, 0)';

    public static function sqlBalance(): string
    {
        return '(GREATEST(' . self::SQL_ITEMS_TOTAL . ' - ' . self::SQL_DISCOUNT . ', 0) - ' . self::SQL_PAID_TOTAL . ')';
    }

    /** Adds bill_grand_total, bill_paid_total, bill_balance columns */
    public function scopeWithBillTotals($query)
    {
        if (is_null($query->getQuery()->columns)) {
            $query->select('jobs.*');
        }

        return $query
            ->selectRaw('GREATEST(' . self::SQL_ITEMS_TOTAL . ' - ' . self::SQL_DISCOUNT . ', 0) AS bill_grand_total')
            ->selectRaw(self::SQL_PAID_TOTAL . ' AS bill_paid_total')
            ->selectRaw(self::sqlBalance() . ' AS bill_balance');
    }

    /** Jobs where the customer still owes money */
    public function scopePendingBills($query)
    {
        return $query->whereRaw(self::sqlBalance() . ' > 0.009');
    }

    public function itemsTotal(): float
    {
        return (float)DB::selectOne('SELECT ' . str_replace('jobs.id', '?', self::SQL_ITEMS_TOTAL) . ' AS t', [$this->id])->t;
    }

    public function grandTotal(): float
    {
        return max(0, $this->itemsTotal() - $this->effectiveDiscount());
    }

    public function paidTotal(): float
    {
        return (float)$this->payments()->sum('amount');
    }

    public function balance(): float
    {
        return round($this->grandTotal() - $this->paidTotal(), 2);
    }

    /**
     * Single source of truth for the discount shown on bills/invoices:
     * the delivery discount once delivered, otherwise the job discount.
     */
    public function effectiveDiscount(): float
    {
        if ($this->delivery) {
            return (float)($this->delivery->discount ?? 0);
        }

        return (float)($this->discount ?? 0);
    }

   
}
