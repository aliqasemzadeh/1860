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
        Schema::create('sepidar_bank_branches', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('BankBranchId')->unique();
            $table->bigInteger('BankRef')->nullable();
            $table->string('Code')->nullable();
            $table->string('Title')->nullable();
            $table->string('Title_En')->nullable();
            $table->bigInteger('LocationRef')->nullable();
            $table->string('Version')->nullable();
            $table->string('Creator')->nullable();
            $table->dateTime('CreationDate')->nullable();
            $table->string('LastModifier')->nullable();
            $table->dateTime('LastModificationDate')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_branches');
    }
};
