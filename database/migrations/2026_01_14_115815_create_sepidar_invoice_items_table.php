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
        Schema::create('sepidar_invoice_items', function (Blueprint $table) {
            $table->id();
			$table->bigInteger('InvoiceItemID')->unique();
			$table->bigInteger('InvoiceRef')->nullable();
			$table->bigInteger('RowID')->nullable();
			$table->bigInteger('ItemRef')->nullable();
			$table->bigInteger('TracingRef')->nullable();
			$table->bigInteger('StockRef')->nullable();
			$table->string('Quantity')->nullable();
			$table->string('SecondaryQuantity')->nullable();
			$table->string('Fee')->nullable();
			$table->string('Price')->nullable();
			$table->string('PriceInBaseCurrency')->nullable();
			$table->string('Discount')->nullable();
			$table->string('DiscountInBaseCurrency')->nullable();
			$table->bigInteger('DiscountItemGroupRef')->nullable();
			$table->string('PriceInfoDiscountRate')->nullable();
			$table->string('PriceInfoPriceDiscount')->nullable();
			$table->string('PriceInfoPercentDiscount')->nullable();
			$table->string('CustomerDiscount')->nullable();
			$table->string('CustomerDiscountRate')->nullable();
			$table->string('AggregateAmountDiscountRate')->nullable();
			$table->string('AggregateAmountPriceDiscount')->nullable();
			$table->string('AggregateAmountPercentDiscount')->nullable();
			$table->string('Addition')->nullable();
			$table->string('AdditionInBaseCurrency')->nullable();
			$table->string('Tax')->nullable();
			$table->string('TaxInBaseCurrency')->nullable();
			$table->string('Duty')->nullable();
			$table->string('DutyInBaseCurrency')->nullable();
			$table->string('AdditionFactor_VatEffective')->nullable();
			$table->string('AdditionFactorInBaseCurrency_VatEffective')->nullable();
			$table->string('AdditionFactor_VatIneffective')->nullable();
			$table->string('AdditionFactorInBaseCurrency_VatIneffective')->nullable();
			$table->string('NetPriceInBaseCurrency')->nullable();
			$table->string('Rate')->nullable();
			$table->bigInteger('QuotationItemRef')->nullable();
			$table->bigInteger('OrderItemRef')->nullable();
			$table->string('Description')->nullable();
			$table->string('Description_En')->nullable();
			$table->bigInteger('DiscountInvoiceItemRef')->nullable();
			$table->bigInteger('ProductPackRef')->nullable();
			$table->string('ProductPackQuantity')->nullable();
			$table->string('BankFeeForCurrencySale')->nullable();
			$table->string('BankFeeForCurrencySaleInBaseCurrency')->nullable();
			$table->string('IsAggregateDiscountInvoiceItem')->nullable();
			$table->string('TaxPayerCurrencyPurchaseRate')->nullable();
			$table->string('NetPrice')->nullable();





            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
