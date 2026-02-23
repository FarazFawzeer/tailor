<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hire_item_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hire_item_id')->constrained('hire_items')->cascadeOnDelete();
            $table->string('image_path'); // storage/...
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hire_item_images');
    }
};