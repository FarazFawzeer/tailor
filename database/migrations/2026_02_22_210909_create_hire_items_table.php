<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hire_items', function (Blueprint $table) {
            $table->id();

            // unique code for each dress/item
            $table->string('item_code', 50)->unique(); // ex: HIRE-SHIRT-0001

            $table->string('name'); // ex: White Suit / Kurtha / Blazer
            $table->string('category')->nullable(); // optional: Suit, Shirt, etc
            $table->string('size', 50)->nullable(); // S/M/L/XL or 40/42 etc
            $table->string('color', 50)->nullable();

            $table->decimal('hire_price', 12, 2)->default(0); // per hire
            $table->decimal('deposit_amount', 12, 2)->default(0);

            // status
            $table->enum('status', ['available', 'reserved', 'hired', 'maintenance'])->default('available');

            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hire_items');
    }
};