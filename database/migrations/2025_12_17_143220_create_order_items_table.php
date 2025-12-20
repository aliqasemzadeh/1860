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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_id');

            // Snapshot of sold item (IMPORTANT: don't rely only on product table)
            $table->string('sku')->nullable();
            $table->string('name'); // نام محصول/واریانت در زمان سفارش

            // Optional links to your catalog (nullable for safety)
            $table->unsignedBigInteger('warranty_id')->nullable();
            $table->unsignedBigInteger('color_id')->nullable();

            $table->unsignedInteger('quantity')->default(1);

            // Money per line
            $table->decimal('unit_price_amount', 18, 2)->default(0); // قیمت واحد
            $table->decimal('discount_amount', 18, 2)->default(0);   // تخفیف آیتم
            $table->decimal('tax_amount', 18, 2)->default(0);        // مالیات آیتم
            $table->decimal('total_amount', 18, 2)->default(0);      // جمع نهایی این ردیف

            // Extra data (selected options مثل رنگ/گارانتی، یا هر دیتای دیگر)
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
