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
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unsignedInteger('max_teams')->nullable();
            $table->unsignedInteger('max_tasks')->nullable();
            $table->unsignedInteger('max_storage_mb')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('max_teams');
            $table->dropColumn('max_tasks');
            $table->dropColumn('max_storage_mb');
        });
    }
};
