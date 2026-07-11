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
            $table->date('grace_period_starts_at')->nullable()->after('ends_at');
            $table->date('grace_period_ends_at')->nullable()->after('grace_period_starts_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('grace_period_starts_at');
            $table->dropColumn('grace_period_ends_at');
        });
    }
};
