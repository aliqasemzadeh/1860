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
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->id();

            // Basic info
            $table->string('name');
            $table->text('description')->nullable();

            // Owner
            $table->bigInteger('owner_user_id');

            // Settings / state
            $table->boolean('is_active')->default(true);
            $table->timestamp('archived_at')->nullable();

            // Metadata (for future use: limits, plan, preferences)
            $table->json('settings')->nullable();

            $table->timestamps();

            $table->softDeletes();

            // Indexes
            $table->index('owner_user_id');
            $table->index('archived_at');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};
