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
        Schema::create('sepidar_bank_account_balances', function (Blueprint $table) {
            $table->id();
			$table->bigInteger('BankAccountBalanceId')->unique();
			$table->string('Balance')->nullable();
			$table->bigInteger('FiscalYearRef')->nullable();
			$table->bigInteger('BankAccountRef')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_account_balances');
    }
};
