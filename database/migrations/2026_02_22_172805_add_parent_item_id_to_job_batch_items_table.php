<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('job_batch_items', function (Blueprint $table) {
            if (!Schema::hasColumn('job_batch_items', 'parent_item_id')) {
                $table->foreignId('parent_item_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('job_batch_items')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_batch_items', function (Blueprint $table) {
            if (Schema::hasColumn('job_batch_items', 'parent_item_id')) {
                $table->dropConstrainedForeignId('parent_item_id');
            }
        });
    }
};