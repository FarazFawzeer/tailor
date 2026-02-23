<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('item_measurement_sets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_batch_item_id')
                ->constrained('job_batch_items')
                ->cascadeOnDelete();

            // null = same measurement for all pieces
            $table->unsignedInteger('piece_no')->nullable();

            $table->foreignId('captured_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();

            $table->timestamps();

            // only one set per item per piece_no
            $table->unique(['job_batch_item_id', 'piece_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_measurement_sets');
    }
};