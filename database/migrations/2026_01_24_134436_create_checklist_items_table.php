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
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('checklist_id');

            $table->string('content');
            $table->boolean('is_done')->default(false);

            $table->unsignedInteger('position')->default(0);

            $table->timestamp('done_at')->nullable();

            // برای اینکه بدانیم چه کسی آیتم را تیک زده (اختیاری ولی کاربردی)
            $table->bigInteger('done_by')
                ->nullable();

            $table->timestamps();

            $table->index(['checklist_id', 'position']);
            $table->index('is_done');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
    }
};
