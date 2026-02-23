<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('measurement_fields', function (Blueprint $table) {
            $table->id();

            $table->foreignId('measurement_template_id')
                ->constrained('measurement_templates')
                ->cascadeOnDelete();

            $table->string('label');           // Chest
            $table->string('key');             // chest (unique within template)
            $table->string('unit')->default('inch'); // inch / cm
            $table->string('input_type')->default('number'); // number/text (future)
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['measurement_template_id', 'sort_order']);
            $table->unique(['measurement_template_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurement_fields');
    }
};