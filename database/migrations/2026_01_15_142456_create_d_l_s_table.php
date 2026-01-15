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
        Schema::create('d_l_s', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('DLId')->unique();
            $table->string('Code')->nullable();
            $table->string('Title')->nullable();
            $table->string('Title_En')->nullable();
            $table->string('Type')->nullable();
            $table->string('Version')->nullable();
            $table->string('Creator')->nullable();
            $table->string('CreationDate')->nullable();
            $table->string('LastModifier')->nullable();
            $table->string('LastModificationDate')->nullable();
            $table->string('IsActive')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('d_l_s');
    }
};
