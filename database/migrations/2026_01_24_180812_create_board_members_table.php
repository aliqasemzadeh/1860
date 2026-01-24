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
        Schema::create('board_members', function (Blueprint $table) {
            $table->id();

            // Board
            $table->bigInteger('board_id');

            // User
            $table->bigInteger('user_id');

            // Optional: role inside board (admin / member / viewer)
            $table->string('role')->default('member');

            // Soft access control
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // هر کاربر فقط یک‌بار عضو هر برد
            $table->unique(['board_id', 'user_id']);

            // Indexes
            $table->index(['board_id', 'is_active']);
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_members');
    }
};
