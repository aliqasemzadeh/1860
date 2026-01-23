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
        Schema::create('labels', function (Blueprint $table) {
            $table->id();


            $table->bigInteger('workspace_id');

            $table->string('name');
            $table->string('color', 32)->nullable(); // hex یا نام رنگ
            $table->timestamps();

            // هر نام لیبل در هر workspace یکتا
            $table->unique(['workspace_id', 'name']);

            $table->index('workspace_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labels');
    }
};
