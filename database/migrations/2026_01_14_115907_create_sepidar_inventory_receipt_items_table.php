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
        Schema::create('sepidar_inventory_receipt_items', function (Blueprint $table) {
        $table->id();
		$table->bigInteger('InventoryReceiptItemId')->unique();
        $table->bigInteger('InventoryReceiptRef')->nullable();
        $table->string('IsReturn')->nullable();
        $table->string('RowNumber')->nullable();
        $table->string('Base')->nullable();
        $table->string('ReturnBase')->nullable();
        $table->bigInteger('ItemRef')->nullable();
        $table->bigInteger('TracingRef')->nullable();
        $table->string('Quantity')->nullable();
        $table->string('SecondaryQuantity')->nullable();
        $table->string('RemainingQuantity')->nullable();
        $table->string('RemainingSecondaryQuantity')->nullable();
        $table->bigInteger('CurrencyRef')->nullable();
        $table->string('CurrencyRate')->nullable();
        $table->string('CurrencyValue')->nullable();
        $table->string('Price')->nullable();
        $table->string('Tax')->nullable();
        $table->string('TaxCurrencyValue')->nullable();
        $table->string('Duty')->nullable();
        $table->string('DutyCurrencyValue')->nullable();
        $table->string('TransportPrice')->nullable();
        $table->string('TransportTax')->nullable();
        $table->string('TransportDuty')->nullable();
        $table->string('TransportDescription')->nullable();
        $table->string('Description')->nullable();
        $table->string('Description_En')->nullable();
        $table->string('Version')->nullable();
        $table->bigInteger('BasePurchaseInvoiceItemRef')->nullable();
        $table->bigInteger('BaseImportPurchaseInvoiceItemRef')->nullable();
        $table->string('ParityCheck')->nullable();
        $table->bigInteger('ProductOrderRef')->nullable();
        $table->string('InventoryDeliveryItemRef')->nullable();
        $table->bigInteger('WeighingRef')->nullable();
        $table->string('ReturnedPrice')->nullable();
        $table->string('OtherCostsAmount')->nullable();
        $table->string('ImportOrderFinalFee')->nullable();
        $table->bigInteger('ServiceInventoryPurchaseInvoiceRef')->nullable();
        $table->string('Fee')->nullable();
        $table->string('ReturnedFee')->nullable();
        $table->string('AllotmenedOtherCostInBaseCurrency')->nullable();
        $table->string('NetPrice')->nullable();
        $table->string('ReturnedNetPrice')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_receipt_items');
    }
};
