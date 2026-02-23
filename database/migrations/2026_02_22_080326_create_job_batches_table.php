<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();

            $table->string('batch_no');                         // BATCH-001, BATCH-002 (per job)
            $table->date('batch_date')->nullable();             // batch created date
            $table->date('due_date')->nullable();               // batch due date
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['job_id', 'batch_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_batches');
    }
};