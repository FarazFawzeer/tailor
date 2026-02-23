<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hire_agreements', function (Blueprint $table) {
            $table->id();

            $table->string('agreement_no', 50)->unique(); // HIRE-000001
            $table->foreignId('customer_id')->constrained('customers');

            $table->date('issue_date');
            $table->date('expected_return_date');

            $table->date('actual_return_date')->nullable();

            $table->decimal('fine_per_day', 12, 2)->default(0);
            $table->decimal('fine_amount', 12, 2)->default(0);

            $table->decimal('deposit_received', 12, 2)->default(0);
            $table->decimal('total_hire_amount', 12, 2)->default(0);

            $table->enum('status', ['issued', 'returned', 'cancelled'])->default('issued');
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hire_agreements');
    }
};