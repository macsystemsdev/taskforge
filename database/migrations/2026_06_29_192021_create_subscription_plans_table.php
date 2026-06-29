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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            
            $table->string('name');
            $table->string('slug')->unique();

            $table->decimal('price', 10, 2);
            $table->string('currency')->default('USD');

            $table->string('billing_interval');

            $table->unsignedInteger('max_workspaces');
            $table->unsignedInteger('max_projects');
            $table->unsignedInteger('max_members');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
