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
        Schema::create('subscription_plan_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('display_name');

            $table->string('subtitle')
                ->nullable();

            $table->text('description')
                ->nullable();

            $table->string('badge')
                ->nullable();

            $table->boolean('popular')
                ->default(false);

            $table->boolean('recommended')
                ->default(false);

            $table->string('accent_color')
                ->nullable();

            $table->integer('card_order')
                ->default(0);

            $table->string('button_text')
                ->nullable();

            $table->text('marketing_copy')
                ->nullable();

            $table->timestamps();

            $table->unique('subscription_plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plan_metadata');
    }
};
