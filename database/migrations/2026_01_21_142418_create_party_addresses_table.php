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
        Schema::create('sepidar_party_addresses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('PartyAddressId')->unique();
            $table->bigInteger('PartyRef')->nullable();
            $table->string('IsMain')->nullable();
            $table->string('Type')->nullable();
            $table->string('Address')->nullable();
            $table->bigInteger('LocationRef')->nullable();
            $table->string('ZipCode')->nullable();
            $table->string('Address_En')->nullable();
            $table->string('Title')->nullable();
            $table->string('Latitude')->nullable();
            $table->string('Longitude')->nullable();
            $table->string('Version')->nullable();
            $table->string('Guid')->nullable();
            $table->string('BranchCode')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sepidar_party_addresses');
    }
};
