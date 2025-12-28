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
        Schema::create('price_fetchers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id');
            $table->enum('type', ['digikala', 'fafait', 'markazi']);
            $table->string('url');
            $table->bigInteger('last_price')->nullable();
            $table->timestamp('last_fetched_at')->nullable();
            $table->timestamps();

            $table->index('product_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_fetchers');
    }
};
