<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dress_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();   // SHIRT, TROUSER
            $table->string('name');            // Shirt, Trouser
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dress_types');
    }
};