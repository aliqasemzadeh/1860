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
        Schema::create('kanban_card_assignees', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('card_id');

            $table->bigInteger('user_id');

            $table->timestamp('assigned_at')->useCurrent();

            $table->timestamps();

            // هر کاربر فقط یک‌بار به هر کارت
            $table->unique(['card_id', 'user_id']);

            // Indexes
            $table->index('user_id');
            $table->index('card_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_assignees');
    }
};
