<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('job_batch_items', function (Blueprint $table) {
            if (!Schema::hasColumn('job_batch_items', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('current_stage_id');
            }
            if (!Schema::hasColumn('job_batch_items', 'completed_by')) {
                $table->foreignId('completed_by')->nullable()->after('completed_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_batch_items', function (Blueprint $table) {
            if (Schema::hasColumn('job_batch_items', 'completed_by')) {
                $table->dropConstrainedForeignId('completed_by');
            }
            if (Schema::hasColumn('job_batch_items', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });
    }
};