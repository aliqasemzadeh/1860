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
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id');
            $table->decimal('price', 18, 5);
            $table->decimal('sale_price', 18, 5)->nullable();
            $table->bigInteger('color_id')->nullable();
            $table->bigInteger('warranty_id')->nullable();
            $table->decimal('quantity')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'color_id', 'warranty_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
