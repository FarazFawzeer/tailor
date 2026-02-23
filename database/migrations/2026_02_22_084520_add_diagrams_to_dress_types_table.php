<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dress_types', function (Blueprint $table) {
            $table->string('diagram_front')->nullable()->after('is_active');
            $table->string('diagram_back')->nullable()->after('diagram_front');
        });
    }

    public function down(): void
    {
        Schema::table('dress_types', function (Blueprint $table) {
            $table->dropColumn(['diagram_front', 'diagram_back']);
        });
    }
};