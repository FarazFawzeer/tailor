<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('item_measurement_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_measurement_set_id')
                ->constrained('item_measurement_sets')
                ->cascadeOnDelete();

            $table->foreignId('measurement_field_id')
                ->constrained('measurement_fields')
                ->cascadeOnDelete();

            $table->string('value')->nullable(); // keep as string (works for number/text)

            $table->timestamps();

            $table->unique(['item_measurement_set_id', 'measurement_field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_measurement_values');
    }
};