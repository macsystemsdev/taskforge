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
        Schema::create('organization_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('members_count')->default(0);
            $table->unsignedInteger('workspaces_count')->default(0);
            $table->unsignedInteger('projects_count')->default(0);
            $table->unsignedInteger('teams_count')->default(0);
            $table->unsignedInteger('tasks_count')->default(0);
            $table->unsignedInteger('storage_used_bytes')->default(0);
            $table->unsignedInteger('stored_files_count')->default(0);
            $table->unsignedInteger('voice_notes_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_usages');
    }
};
