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
        Schema::create('inventory_receipts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('InventoryReceiptID')->unique();
            $table->string('IsReturn')->nullable();
            $table->string('Type')->nullable();
            $table->string('PurchaseType')->nullable();
            $table->bigInteger('StockRef')->nullable();
            $table->bigInteger('DelivererDLRef')->nullable();
            $table->bigInteger('SLAccountRef')->nullable();
            $table->string('Number')->nullable();
            $table->dateTime('Date')->nullable();
            $table->bigInteger('AccountingVoucherRef')->nullable();
            $table->bigInteger('TransportBrokerSLAccountRef')->nullable();
            $table->bigInteger('TransporterDLRef')->nullable();
            $table->string('TotalPrice')->nullable();
            $table->string('TotalTax')->nullable();
            $table->string('TotalDuty')->nullable();
            $table->string('TotalTransportPrice')->nullable();
            $table->string('TotalNetPrice')->nullable();
            $table->bigInteger('FiscalYearRef')->nullable();
            $table->string('CreatorForm')->nullable();
            $table->string('Creator')->nullable();
            $table->dateTime('CreationDate')->nullable();
            $table->string('LastModifier')->nullable();
            $table->dateTime('LastModificationDate')->nullable();
            $table->string('Version')->nullable();
            $table->bigInteger('BasePurchaseInvoiceRef')->nullable();
            $table->bigInteger('BaseInventoryDeliveryRef')->nullable();
            $table->bigInteger('BaseImportPurchaseInvoiceRef')->nullable();
            $table->string('TotalReturnedPrice')->nullable();
            $table->string('TotalReturnedNetPrice')->nullable();
            $table->string('Description')->nullable();
            $table->string('TotalOtherCost')->nullable();
            $table->string('IsWastage')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_receipts');
    }
};
