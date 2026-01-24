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
        Schema::create('card_events', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('card_id');

            // کسی که این رویداد را ایجاد کرده (کاربر/سیستم)
            $table->bigInteger('actor_user_id')
                ->nullable();

            // نوع رویداد: created/moved/updated/commented/assigned/label_added/archived/...
            $table->string('type', 50);

            // برای رویدادهای move
            $table->bigInteger('from_column_id')
                ->nullable();

            $table->bigInteger('to_column_id')
                ->nullable();

            // اگر swimlane هم داری و رویداد مربوط به تغییر آن باشد
            $table->bigInteger('from_swimlane_id')
                ->nullable();

            $table->bigInteger('to_swimlane_id')
                ->nullable();

            // جزئیات رویداد: before/after، تغییرات فیلدها، متن کامنت، لیبل‌ها، ...
            $table->json('payload')->nullable();

            // ایندکس‌پذیر برای گزارش‌ها (بدون نیاز به created_at در pivotها)
            $table->timestamp('occurred_at')->useCurrent();

            $table->timestamps();

            // Indexes
            $table->index(['card_id', 'occurred_at']);
            $table->index(['type', 'occurred_at']);
            $table->index(['to_column_id', 'occurred_at']);
            $table->index(['actor_user_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_events');
    }
};
