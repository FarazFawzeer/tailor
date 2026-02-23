<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('measurement_templates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dress_type_id')->constrained('dress_types')->cascadeOnDelete();
            $table->string('name'); // Ex: Normal / Slim Fit / Kids
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['dress_type_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurement_templates');
    }
};