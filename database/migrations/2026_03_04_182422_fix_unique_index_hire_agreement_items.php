<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hire_agreement_items', function (Blueprint $table) {

            // If "size" column not exists, add it (skip if already added earlier)
            if (!Schema::hasColumn('hire_agreement_items', 'size')) {
                $table->string('size', 50)->nullable()->after('hire_item_id');
            }
            if (!Schema::hasColumn('hire_agreement_items', 'qty')) {
                $table->unsignedInteger('qty')->default(1)->after('size');
            }
            if (!Schema::hasColumn('hire_agreement_items', 'line_total')) {
                $table->decimal('line_total', 12, 2)->default(0)->after('deposit_amount');
            }

            // Drop old unique index (name may vary)
            // Common Laravel name:
            // hire_agreement_items_hire_agreement_id_hire_item_id_unique
            $table->dropUnique('hire_agreement_items_hire_agreement_id_hire_item_id_unique');

            // Add new unique including size
            $table->unique(['hire_agreement_id', 'hire_item_id', 'size'], 'hai_agreement_item_size_unique');
        });
    }

    public function down(): void
    {
        Schema::table('hire_agreement_items', function (Blueprint $table) {
            $table->dropUnique('hai_agreement_item_size_unique');
            $table->unique(['hire_agreement_id', 'hire_item_id'], 'hire_agreement_items_hire_agreement_id_hire_item_id_unique');
        });
    }
};