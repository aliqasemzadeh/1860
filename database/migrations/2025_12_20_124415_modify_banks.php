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
        Schema::table('banks', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
            $table->string('number')->nullable()->after('name');
            $table->string('iban')->nullable()->after('name');
            $table->string('card_number')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->dropColumn('code');
            $table->dropColumn('number');
            $table->dropColumn('iban');
            $table->dropColumn('card_number');
        });
    }
};
