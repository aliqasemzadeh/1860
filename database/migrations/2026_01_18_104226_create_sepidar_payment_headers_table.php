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
        Schema::create('sepidar_payment_headers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('PaymentHeaderId')->unique();
            $table->string('Type')->nullable();
            $table->bigInteger('AccountSlRef')->nullable();
            $table->bigInteger('DlRef')->nullable();
            $table->string('Number')->nullable();
            $table->dateTime('Date')->nullable();
            $table->bigInteger('CurrencyRef')->nullable();
            $table->string('Description')->nullable();
            $table->string('Description_En')->nullable();
            $table->string('Discount')->nullable();
            $table->string('TotalAmount')->nullable();
            $table->string('ItemType')->nullable();
            $table->string('State')->nullable();
            $table->string('CreatorForm')->nullable();
            $table->string('Creator')->nullable();
            $table->dateTime('CreationDate')->nullable();
            $table->string('LastModifier')->nullable();
            $table->dateTime('LastModificationDate')->nullable();
            $table->string('Version')->nullable();
            $table->string('Rate')->nullable();
            $table->bigInteger('CashRef')->nullable();
            $table->string('Amount')->nullable();
            $table->string('AmountInBaseCurrency')->nullable();
            $table->bigInteger('ReceiptHeaderRef')->nullable();
            $table->bigInteger('FiscalYearRef')->nullable();
            $table->bigInteger('VoucherRef')->nullable();
            $table->string('DiscountRate')->nullable();
            $table->string('DiscountInBaseCurrency')->nullable();
            $table->bigInteger('BankFeeSlRef')->nullable();
            $table->string('TotalAmountInBaseCurrency')->nullable();
            $table->string('PaymentAmount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sepidar_payment_headers');
    }
};
