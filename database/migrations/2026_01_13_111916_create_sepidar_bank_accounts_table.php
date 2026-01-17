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
        Schema::create('sepidar_bank_accounts', function (Blueprint $table) {
            $table->id();
			$table->bigInteger('BankAccountId')->unique();
			$table->bigInteger('BankBranchRef')->nullable();
			$table->bigInteger('DlRef')->nullable();
			$table->string('AccountNo')->nullable();
			$table->string('Version')->nullable();
			$table->string('ClearFormatName')->nullable();
			$table->bigInteger('AccountTypeRef')->nullable();
			$table->bigInteger('CurrencyRef')->nullable();
			$table->string('Rate')->nullable();
			$table->string('FirstAmount')->nullable();
			$table->dateTime('FirstDate')->nullable();
			$table->string('Creator')->nullable();
			$table->dateTime('CreationDate')->nullable();
			$table->string('LastModifier')->nullable();
			$table->dateTime('LastModificationDate')->nullable();
			$table->string('Balance')->nullable();
			$table->string('BillFirstAmount')->nullable();
			$table->string('BlockedAmount')->nullable();
			$table->string('Owner')->nullable();
			$table->string('Owner_En')->nullable();
			$table->string('ShowBankFeeSeparately')->nullable();
			$table->string('CreditCardNumber')->nullable();
			$table->string('ShebaNumber')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
