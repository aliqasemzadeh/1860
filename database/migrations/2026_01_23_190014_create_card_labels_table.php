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
        Schema::create('card_labels', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('card_id');

            $table->bigInteger('label_id');

            $table->timestamps();

            // جلوگیری از تکرار
            $table->unique(['card_id', 'label_id']);

            // Indexes
            $table->index('card_id');
            $table->index('label_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_labels');
    }
};
