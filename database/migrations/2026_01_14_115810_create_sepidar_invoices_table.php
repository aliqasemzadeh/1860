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
        Schema::create('sepidar_invoices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('InvoiceId')->unique();
            $table->bigInteger('QuotationRef')->nullable();
            $table->bigInteger('OrderRef')->nullable();
            $table->bigInteger('CustomerPartyRef')->nullable();
            $table->string('CustomerRealName')->nullable();
            $table->string('CustomerRealName_En')->nullable();
            $table->bigInteger('SaleTypeRef')->nullable();
            $table->bigInteger('PartyAddressRef')->nullable();
            $table->string('Number')->nullable();
            $table->dateTime('Date')->nullable();
            $table->bigInteger('CurrencyRef')->nullable();
            $table->bigInteger('SLRef')->nullable();
            $table->bigInteger('DeliveryLocationRef')->nullable();
            $table->string('State')->nullable();
            $table->string('Price')->nullable();
            $table->string('PriceInBaseCurrency')->nullable();
            $table->string('Discount')->nullable();
            $table->string('DiscountInBaseCurrency')->nullable();
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
            $table->bigInteger('SignatureRef')->nullable();
            $table->string('Rate')->nullable();
            $table->string('Version')->nullable();
            $table->string('Creator')->nullable();
            $table->string('CreationDate')->nullable();
            $table->string('LastModifier')->nullable();
            $table->dateTime('LastModificationDate')->nullable();
            $table->bigInteger('FiscalYearRef')->nullable();
            $table->bigInteger('VoucherRef')->nullable();
            $table->string('ShouldControlCustomerCredit')->nullable();
            $table->string('Guid')->nullable();
            $table->string('BaseOnInventoryDelivery')->nullable();
            $table->bigInteger('AgreementRef')->nullable();
            $table->dateTime('TaxPayerBillIssueDateTime')->nullable();
            $table->string('SettlementType')->nullable();
            $table->string('Description')->nullable();
            $table->string('NetPrice')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
