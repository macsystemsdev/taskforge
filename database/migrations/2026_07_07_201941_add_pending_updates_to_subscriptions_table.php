<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('pending_subscription_plan_id')
                ->nullable()
                ->constrained('subscription_plans')
                ->nullOnDelete();

            $table->timestamp('pending_effective_at')
                ->nullable();

            $table->foreignId('pending_payment_transaction_id')
                ->nullable()
                ->constrained('payment_transactions')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['pending_subscription_plan_id']);
            $table->dropColumn('pending_subscription_plan_id');

            $table->dropColumn('pending_effective_at');

            $table->dropForeign(['pending_payment_transaction_id']);
            $table->dropColumn('pending_payment_transaction_id');
        });
    }
};
