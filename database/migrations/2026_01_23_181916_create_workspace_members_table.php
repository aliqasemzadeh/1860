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
        Schema::create('kanban_workspace_members', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->bigInteger('workspace_id');

            $table->bigInteger('user_id');

            // Role inside workspace
            $table->string('role')->default('member');
            // roles example: owner | admin | member | viewer

            // Optional flags
            $table->boolean('is_active')->default(true);

            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            // Constraints
            $table->unique(['workspace_id', 'user_id']);

            // Indexes
            $table->index('role');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_members');
    }
};
