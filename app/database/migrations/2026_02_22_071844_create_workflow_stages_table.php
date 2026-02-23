<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('workflow_stages', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();   // CUT, SEWING, BUTTON, IRONING, PACKAGING
            $table->string('name');            // Cut, Sewing, Button, Ironing, Packaging
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_stages');
    }
};