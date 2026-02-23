<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('job_batch_items', function (Blueprint $table) {
            if (!Schema::hasColumn('job_batch_items', 'current_stage_id')) {
                $table->foreignId('current_stage_id')
                    ->nullable()
                    ->after('qty')
                    ->constrained('workflow_stages')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_batch_items', function (Blueprint $table) {
            if (Schema::hasColumn('job_batch_items', 'current_stage_id')) {
                $table->dropConstrainedForeignId('current_stage_id');
            }
        });
    }
};