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
        Schema::create('kanban_columns', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('board_id');

            // Column info
            $table->string('name');
            $table->unsignedInteger('position'); // ترتیب نمایش ستون‌ها
            $table->unsignedInteger('wip_limit')->nullable(); // محدودیت WIP

            // Behavior flags
            $table->boolean('is_done')->default(false);   // ستون نهایی؟
            $table->boolean('is_blocked')->default(false); // ستون Blocked (اختیاری)

            // Optional column-level settings
            $table->json('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['board_id', 'position']);
            $table->index('is_done');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('columns');
    }
};
