<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('payment_type', 20)->default('payment');  // advance | payment | settlement
            $table->string('method', 20)->default('cash');           // cash | card | bank | other
            $table->dateTime('paid_at');

            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['job_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_payments');
    }
};
