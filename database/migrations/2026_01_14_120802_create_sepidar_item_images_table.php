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
        Schema::create('sepidar_item_images', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('ItemImageID')->unique();
            $table->bigInteger('ItemRef')->nullable();
            $table->longText('Image')->nullable();
            $table->longText('Thumbnail')->nullable();
            $table->string('Version')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_images');
    }
};
