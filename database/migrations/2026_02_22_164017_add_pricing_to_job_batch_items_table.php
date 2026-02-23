<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('job_batch_items', function (Blueprint $table) {
            if (!Schema::hasColumn('job_batch_items', 'unit_price')) {
                $table->decimal('unit_price', 12, 2)->default(0)->after('qty');
            }
            if (!Schema::hasColumn('job_batch_items', 'line_total')) {
                $table->decimal('line_total', 12, 2)->default(0)->after('unit_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_batch_items', function (Blueprint $table) {
            if (Schema::hasColumn('job_batch_items', 'line_total')) $table->dropColumn('line_total');
            if (Schema::hasColumn('job_batch_items', 'unit_price')) $table->dropColumn('unit_price');
        });
    }
};