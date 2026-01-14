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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
			$table->bigInteger('BankId')->unique();
			$table->string('Title')->nullable();
			$table->string('Title_En')->nullable();
			$table->string('Version')->nullable();
			$table->string('Creator')->nullable();
			$table->dateTime('CreationDate')->nullable();
			$table->string('LastModifier')->nullable();
			$table->dateTime('LastModificationDate')->nullable();
			$table->string('TaxFileCode')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
