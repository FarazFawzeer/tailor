<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hire_agreements', function (Blueprint $table) {

            // If you already have deposit_received, keep it as-is.
            // This is the NEW paid accumulator:
            $table->decimal('amount_paid', 12, 2)->default(0)->after('deposit_received');

            // Optional: store last payment info (simple)
            $table->string('payment_method', 30)->nullable()->after('amount_paid');
            $table->string('payment_reference', 100)->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('hire_agreements', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'payment_method', 'payment_reference']);
        });
    }
};