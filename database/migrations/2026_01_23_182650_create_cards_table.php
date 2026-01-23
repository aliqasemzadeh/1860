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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->bigInteger('board_id');

            $table->bigInteger('column_id');

            // Optional: if you implement swimlanes later
            $table->bigInteger('swimlane_id')->nullable();

            // Content
            $table->string('title');
            $table->longText('description')->nullable(); // markdown/text

            $table->bigInteger('modelable_id')->nullable();
            $table->string('modelable_type')->nullable();

            // Ordering inside the column (drag & drop)
            $table->unsignedInteger('position');

            // Priority: 1 highest -> bigger numbers lower (or reverse, up to you)
            $table->unsignedTinyInteger('priority')->default(3);

            // Dates
            $table->timestamp('due_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Ownership / audit
            $table->bigInteger('created_by');

            // Archive instead of delete
            $table->timestamp('archived_at')->nullable();

            // Flexible metadata: estimate, external links, provider ids, etc.
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes (performance-critical)
            $table->index(['board_id', 'archived_at']);
            $table->index(['column_id', 'archived_at']);
            $table->index(['board_id', 'column_id', 'position']);
            $table->index('due_at');
            $table->index('priority');
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
