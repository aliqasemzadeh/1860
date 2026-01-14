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
        Schema::create('item_stock_summaries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('ItemStockSummaryID')->unique();
            $table->bigInteger('StockRef')->nullable();
            $table->bigInteger('ItemRef')->nullable();
            $table->bigInteger('TracingRef')->nullable();
            $table->string('Order')->nullable();
            $table->bigInteger('UnitRef')->nullable();
            $table->string('InputQuantity')->nullable();
            $table->string('OutputQuantity')->nullable();
            $table->string('Quantity')->nullable();
            $table->string('SaleQuantity')->nullable();
            $table->string('SaleWithReserveQuantity')->nullable();
            $table->bigInteger('FiscalYearRef')->nullable();
            $table->string('FeedFromClosingOperation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_stock_summaries');
    }
};
