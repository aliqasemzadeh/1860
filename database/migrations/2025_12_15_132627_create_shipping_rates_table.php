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
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('shipping_method_id');

            $table->bigInteger('shipping_zone_id');

            $table->enum('rate_type', ['flat', 'weight', 'price'])
                ->default('flat');

            $table->decimal('amount', 12, 2); // هزینه ارسال

            $table->decimal('min_weight', 8, 2)->nullable(); // kg
            $table->decimal('max_weight', 8, 2)->nullable();

            $table->decimal('min_price', 12, 2)->nullable(); // مبلغ سفارش
            $table->decimal('max_price', 12, 2)->nullable();

            $table->string('estimated_days')->nullable(); // "2-3 روز"

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['shipping_method_id', 'shipping_zone_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
