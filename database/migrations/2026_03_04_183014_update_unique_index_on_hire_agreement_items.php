<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hire_agreement_items', function (Blueprint $table) {
            // Drop OLD unique (agreement_id + item_id)
            // This is your current error index name from message:
            $table->dropUnique('hire_agreement_items_hire_agreement_id_hire_item_id_unique');

            // Add NEW unique (agreement_id + item_id + size)
            $table->unique(
                ['hire_agreement_id', 'hire_item_id', 'size'],
                'hai_agreement_item_size_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('hire_agreement_items', function (Blueprint $table) {
            $table->dropUnique('hai_agreement_item_size_unique');

            $table->unique(
                ['hire_agreement_id', 'hire_item_id'],
                'hire_agreement_items_hire_agreement_id_hire_item_id_unique'
            );
        });
    }
};