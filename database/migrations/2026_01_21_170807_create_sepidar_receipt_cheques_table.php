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
        Schema::create('receipt_cheques', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('ReceiptChequeId')->unique();
            $table->string('Number')->nullable();
            $table->string('SecondNumber')->nullable();
            $table->string('SayadCode')->nullable();
            $table->string('IsGuarantee')->nullable();
            $table->string('AccountNo')->nullable();
            $table->string('Amount')->nullable();
            $table->dateTime('Date')->nullable();
            $table->string('Description')->nullable();
            $table->string('Description_En')->nullable();
            $table->string('Version')->nullable();
            $table->string('State')->nullable();
            $table->bigInteger('CashRef')->nullable();
            $table->bigInteger('ReceiptHeaderRef')->nullable();
            $table->bigInteger('BankRef')->nullable();
            $table->string('BranchCode')->nullable();
            $table->string('BranchTitle')->nullable();
            $table->bigInteger('LocationRef')->nullable();
            $table->string('HeaderNumber')->nullable();
            $table->dateTime('HeaderDate')->nullable();
            $table->bigInteger('CurrencyRef')->nullable();
            $table->string('Rate')->nullable();
            $table->string('AmountInBaseCurrency')->nullable();
            $table->bigInteger('BankBranchRef')->nullable();
            $table->bigInteger('DlRef')->nullable();
            $table->string('Type')->nullable();
            $table->string('ChequeOwner')->nullable();
            $table->string('InitState')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt_cheques');
    }
};
