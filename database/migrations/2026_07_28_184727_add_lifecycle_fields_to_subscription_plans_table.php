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
            $table->string('status')
                ->default('draft')
                ->after('is_active');

            $table->timestamp('activated_at')
                ->nullable()
                ->after('status');

            $table->timestamp('retired_at')
                ->nullable()
                ->after('activated_at');

            $table->timestamp('retirement_effective_at')
                ->nullable()
                ->after('retired_at');

            $table->timestamp('archived_at')
                ->nullable()
                ->after('retirement_effective_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'activated_at',
                'retired_at',
                'retirement_effective_at',
                'archived_at',
            ]);
        });
    }
};
