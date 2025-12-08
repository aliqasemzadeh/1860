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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('slug_fa')->unique();
            $table->string('file_path');
            $table->string('file_name');
            $table->double('weight')->default(0);
            $table->double('x_dimension')->default(0);
            $table->double('y_dimension')->default(0);
            $table->double('z_dimension')->default(0);
            $table->bigInteger('category_id');
            $table->bigInteger('brand_id');
            $table->bigInteger('unit_id');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'brand_id', 'unit_id', 'slug', 'slug_fa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
