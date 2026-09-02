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
        Schema::create('torob_price_setters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_fetcher_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('product_price_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('own_shop_names');
            $table->unsignedBigInteger('step_amount');
            $table->unsignedBigInteger('min_price');
            $table->unsignedBigInteger('max_price');
            $table->boolean('is_active')->default(true)->index();
            $table->string('status')->default('idle')->index();
            $table->string('last_competitor_shop')->nullable();
            $table->unsignedBigInteger('last_competitor_price')->nullable();
            $table->unsignedBigInteger('last_target_price')->nullable();
            $table->unsignedBigInteger('last_applied_price')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_changed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('torob_price_setters');
    }
};
