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
        Schema::create('kanban_comments', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('card_id');

            $table->bigInteger('user_id')
                ->nullable();

            // برای reply/thread (اختیاری)
            $table->bigInteger('parent_id')
                ->nullable();

            $table->longText('body'); // متن کامنت (markdown/text)

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['card_id', 'created_at']);
            $table->index('user_id');
            $table->index('parent_id');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
