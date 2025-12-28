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
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('attribute_id')
                ->constrained('attributes')
                ->cascadeOnDelete();

            // Typed storage (choose based on attribute.type)
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 20, 6)->nullable();
            $table->boolean('value_bool')->nullable();
            $table->date('value_date')->nullable();
            $table->json('value_json')->nullable(); // for multiselect or structured values

            $table->timestamps();

            $table->unique(['product_id', 'attribute_id']);

            // Helpful indexes for filtering/search
            $table->index(['attribute_id']);
            $table->index(['attribute_id', 'value_number']);
            $table->index(['attribute_id', 'value_bool']);
            $table->index(['attribute_id', 'value_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
    }
};
