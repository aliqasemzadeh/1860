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
        Schema::create('kanban_boards', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('workspace_id');

            $table->string('name');
            $table->text('description')->nullable();

            // private: فقط اعضای برد/ورک‌اسپیس
            // workspace: همه اعضای ورک‌اسپیس
            // public: عمومی (اگر بعداً خواستی)
            $table->string('visibility')->default('workspace');

            $table->bigInteger('creator_user_id');

            // Board-level settings (مثلاً WIP پیش‌فرض، انواع ستون‌ها، نمایش swimlane و...)
            $table->json('settings')->nullable();

            $table->timestamp('archived_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['workspace_id', 'archived_at']);
            $table->index('visibility');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boards');
    }
};
