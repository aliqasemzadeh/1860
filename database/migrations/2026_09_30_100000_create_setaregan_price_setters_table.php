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
        Schema::create('setaregan_price_setters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_fetcher_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('product_price_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('margin_amount');
            $table->unsignedBigInteger('min_price');
            $table->unsignedBigInteger('max_price');
            $table->unsignedInteger('default_quantity')->default(1);
            $table->boolean('is_active')->default(true)->index();
            $table->string('status')->default('idle')->index();
            $table->unsignedBigInteger('last_supplier_price')->nullable();
            $table->boolean('last_supplier_available')->nullable();
            $table->unsignedBigInteger('last_target_price')->nullable();
            $table->unsignedBigInteger('last_applied_price')->nullable();
            $table->string('last_stock_action')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_changed_at')->nullable();
            $table->timestamp('last_stock_changed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setaregan_price_setters');
    }
};
