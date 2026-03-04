<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hire_item_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hire_item_id')->constrained('hire_items')->cascadeOnDelete();

            $table->string('size', 50);                // ex: L / 42
            $table->unsignedInteger('qty')->default(0); // ex: 3

            // optional (keep if you want per-variant differences)
            $table->string('color', 50)->nullable();    // only if color varies by size
            $table->decimal('hire_price', 12, 2)->nullable();     // override item hire_price
            $table->decimal('deposit_amount', 12, 2)->nullable(); // override item deposit

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // avoid duplicate size rows for same item (if you want)
            $table->unique(['hire_item_id', 'size', 'color']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hire_item_variants');
    }
};