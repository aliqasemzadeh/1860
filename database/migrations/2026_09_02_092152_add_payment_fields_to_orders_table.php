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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_gateway')->nullable()->after('meta');
            $table->string('payment_transaction_id')->nullable()->after('payment_gateway');
            $table->string('payment_reference_id')->nullable()->after('payment_transaction_id');
            $table->string('payment_card_pan')->nullable()->after('payment_reference_id');
            $table->string('payment_ip', 45)->nullable()->after('payment_card_pan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_gateway',
                'payment_transaction_id',
                'payment_reference_id',
                'payment_card_pan',
                'payment_ip',
            ]);
        });
    }
};
