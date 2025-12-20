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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Customer
            $table->bigInteger('user_id')->nullable();

            // Public order identifier
            $table->string('order_number')->unique(); // مثلا: ORD-2025-000001

            // Status
            $table->string('status')->default('pending');

            // Currency & money
            $table->string('currency', 3)->default('IRT');

            $table->decimal('subtotal_amount', 18, 2)->default(0); // جمع اقلام قبل از تخفیف/مالیات
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);

            // Shipping (Freeze at order time)
            $table->bigInteger('shipping_method_id')->nullable();

            $table->bigInteger('shipping_zone_id')->nullable();

            $table->decimal('shipping_amount', 18, 2)->default(0);
            $table->string('shipping_estimated_days')->nullable();

            // Totals
            $table->decimal('total_amount', 18, 2)->default(0);

            // Addresses (snapshot)
            $table->json('shipping_address')->nullable();
            $table->json('billing_address')->nullable();

            // Optional notes / metadata
            $table->text('customer_note')->nullable();
            $table->json('meta')->nullable();

            // Timestamps for lifecycle (optional but useful)
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
