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
        Schema::create('sepidar_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('ItemID')->unique();
            $table->string('Type')->nullable();
            $table->string('Code')->nullable();
            $table->string('Title')->nullable();
            $table->string('Title_En')->nullable();
            $table->string('BarCode')->nullable();
            $table->bigInteger('UnitRef')->nullable();
            $table->bigInteger('SecondaryUnitRef')->nullable();
            $table->bigInteger('SaleUnitRef')->nullable();
            $table->string('IsUnitRatioConstant')->nullable();
            $table->string('UnitsRatio')->nullable();
            $table->string('MinimumAmount')->nullable();
            $table->string('MaximumAmount')->nullable();
            $table->string('ConsumerFee')->nullable();
            $table->string('CanHaveTracing')->nullable();
            $table->bigInteger('TracingCategoryRef')->nullable();
            $table->string('IsPricingBasedOnTracing')->nullable();
            $table->string('TaxExempt')->nullable();
            $table->string('TaxExemptPurchase')->nullable();
            $table->string('Sellable')->nullable();
            $table->bigInteger('DefaultStockRef')->nullable();
            $table->bigInteger('PurchaseGroupRef')->nullable();
            $table->string('SaleGroupRef')->nullable();
            $table->bigInteger('CompoundBarcodeRef')->nullable();
            $table->bigInteger('ItemCategoryRef')->nullable();
            $table->string('Creator')->nullable();
            $table->dateTime('CreationDate')->nullable();
            $table->string('LastModifier')->nullable();
            $table->dateTime('LastModificationDate')->nullable();
            $table->string('Version')->nullable();
            $table->string('IsActive')->nullable();
            $table->bigInteger('AccountSLRef')->nullable();
            $table->string('TaxRate')->nullable();
            $table->string('DutyRate')->nullable();
            $table->bigInteger('CodingGroupRef')->nullable()->index();
            $table->string('SerialTracking')->nullable();
            $table->string('Weight')->nullable();
            $table->string('Volume')->nullable();
            $table->string('IranCode')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
