<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hire_agreement_items', function (Blueprint $table) {
            $table->string('size', 50)->nullable()->after('hire_item_id');
            $table->unsignedInteger('qty')->default(1)->after('size');
            $table->decimal('line_total', 12, 2)->default(0)->after('deposit_amount');
        });
    }

    public function down(): void
    {
        Schema::table('hire_agreement_items', function (Blueprint $table) {
            $table->dropColumn(['size','qty','line_total']);
        });
    }
};