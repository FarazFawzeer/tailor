<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_batch_items', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_cutter_id')->nullable()->after('current_stage_id');

            $table->foreign('assigned_cutter_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_batch_items', function (Blueprint $table) {
            $table->dropForeign(['assigned_cutter_id']);
            $table->dropColumn('assigned_cutter_id');
        });
    }
};