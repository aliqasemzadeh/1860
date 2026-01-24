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
        Schema::create('kanban_swimlanes', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('board_id');

            $table->string('name');
            $table->unsignedInteger('position');

            // Optional: for hiding/removing without deleting
            $table->timestamp('archived_at')->nullable();

            // Optional: future settings (color, type, filters, SLA, etc.)
            $table->json('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['board_id', 'position']);
            $table->index(['board_id', 'archived_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swimlanes');
    }
};
