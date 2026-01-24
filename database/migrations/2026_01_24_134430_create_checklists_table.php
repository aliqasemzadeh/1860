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
        Schema::create('kanban_checklists', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('card_id');

            $table->string('title')->default('Checklist');
            $table->unsignedInteger('position')->default(0);

            $table->timestamps();

            $table->index(['card_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklists');
    }
};
