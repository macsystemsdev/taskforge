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
        Schema::create('stored_files', function (Blueprint $table) {

            $table->id();

            $table->foreignId('organization_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('workspace_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('disk', 50);

            $table->string('path');

            $table->string('stored_name');

            $table->string('original_name');

            $table->string('mime_type', 100);

            $table->string('extension', 20);

            $table->string('category', 30);

            $table->string('visibility', 30);

            $table->unsignedBigInteger('size');

            $table->char('checksum', 64);

            $table->timestamps();

            $table->index('organization_id');

            $table->index('workspace_id');

            $table->index('uploaded_by');

            $table->index('visibility');

            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stored_files');
    }
};
