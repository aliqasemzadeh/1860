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
        Schema::create('groupings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('GroupingID')->unique();
            $table->string('EntityType')->nullable()->index();
            $table->string('Code')->nullable();
            $table->string('FullCode')->nullable();
            $table->string('Title')->nullable();
            $table->string('TitleEn')->nullable();
            $table->string('MaximumCredit')->nullable();
            $table->string('HasCredit')->nullable();
            $table->string('CreditCheckingType')->nullable();
            $table->string('Version')->nullable();
            $table->string('Creator')->nullable();
            $table->datetime('CreationDate')->nullable();
            $table->string('LastModifier')->nullable();
            $table->datetime('LastModificationDate')->nullable();
            $table->bigInteger('CalculationFormulaRef')->nullable();
            $table->bigInteger('ParentGroupRef')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groupings');
    }
};
