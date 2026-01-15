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
        Schema::create('sepidar_parties', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('PartyId')->unique();
            $table->bigInteger('Type')->nullable();
            $table->string('SubType')->nullable();
            $table->string('Name')->nullable();
            $table->string('LastName')->nullable();
            $table->string('Name_En')->nullable();
            $table->string('LastName_En')->nullable();
            $table->string('EconomicCode')->nullable();
            $table->string('IdentificationCode')->nullable();
            $table->string('RegistrationCode')->nullable();
            $table->string('Website')->nullable();
            $table->string('Email')->nullable();
            $table->string('DLRef')->nullable();
            $table->string('IsInBlacklist')->nullable();
            $table->string('IsVendor')->nullable();
            $table->bigInteger('VendorGroupingRef')->nullable();
            $table->string('IsBroker')->nullable();
            $table->bigInteger('BrokerGroupingRef')->nullable();
            $table->string('CommissionRate')->nullable();
            $table->string('BrokerOpeningBalance')->nullable();
            $table->string('BrokerOpeningBalanceType')->nullable();
            $table->string('IsEmployee')->nullable();
            $table->bigInteger('SalespersonPartyRef')->nullable();
            $table->string('DiscountRate')->nullable();
            $table->string('MaximumCredit')->nullable();
            $table->bigInteger('CustomerGroupingRef')->nullable();
            $table->string('CustomerCategoryForTax')->nullable();
            $table->dateTime('BirthDate')->nullable();
            $table->dateTime('MarriageDate')->nullable();
            $table->string('HasCredit')->nullable();
            $table->string('CreditCheckingType')->nullable();
            $table->string('Version')->nullable();
            $table->string('Creator')->nullable();
            $table->dateTime('CreationDate')->nullable();
            $table->string('LastModifier')->nullable();
            $table->dateTime('LastModificationDate')->nullable();
            $table->string('Guid')->nullable();
            $table->string('CustomerRemaining')->nullable();
            $table->string('PassportNumber')->nullable();
            $table->string('IsPurchasingAgent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parties');
    }
};
