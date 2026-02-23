<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hire_agreement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hire_agreement_id')->constrained('hire_agreements')->cascadeOnDelete();
            $table->foreignId('hire_item_id')->constrained('hire_items')->restrictOnDelete();

            $table->decimal('hire_price', 12, 2)->default(0);
            $table->decimal('deposit_amount', 12, 2)->default(0);

            $table->timestamps();

            $table->unique(['hire_agreement_id', 'hire_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hire_agreement_items');
    }
};