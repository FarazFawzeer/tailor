<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();

            $table->string('job_no')->unique();                 // JOB-000001
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            $table->date('job_date')->nullable();               // order date
            $table->date('due_date')->nullable();               // expected delivery date

            $table->text('notes')->nullable();

            // for future: current stage tracking
            $table->foreignId('current_stage_id')->nullable()->constrained('workflow_stages')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};