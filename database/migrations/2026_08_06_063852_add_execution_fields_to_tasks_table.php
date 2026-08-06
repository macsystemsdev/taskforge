<?php

use App\Domain\Task\TaskPriority;
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
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('priority')
                ->default(TaskPriority::MEDIUM->value);

            $table->timestamp('started_at')
                ->nullable();

            $table->timestamp('blocked_at')
                ->nullable();

            $table->text('blocked_reason')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('priority');
            $table->dropColumn('started_at');
            $table->dropColumn('blocked_at');
            $table->dropColumn('blocked_reason');
        });
    }
};
