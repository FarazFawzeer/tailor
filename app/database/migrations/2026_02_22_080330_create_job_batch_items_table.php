<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_batch_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_batch_id')->constrained('job_batches')->cascadeOnDelete();

            $table->foreignId('dress_type_id')->constrained('dress_types')->restrictOnDelete();
            $table->foreignId('measurement_template_id')->nullable()->constrained('measurement_templates')->nullOnDelete();

            $table->unsignedInteger('qty')->default(1);

            // key requirement:
            // same measurement for all pieces OR different per piece
            $table->boolean('per_piece_measurement')->default(false);

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_batch_items');
    }
};