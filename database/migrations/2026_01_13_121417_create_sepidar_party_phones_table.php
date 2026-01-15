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
        Schema::create('sepidar_party_phones', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('PartyPhoneId')->unique();
            $table->bigInteger('PartyRef')->nullable();
            $table->string('IsMain')->nullable();
            $table->string('Type')->nullable();
		    $table->string('Phone')->nullable();
		    $table->string('Version')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('party_phones');
    }
};
