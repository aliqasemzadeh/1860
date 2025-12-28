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
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attribute_group_id')
                ->nullable()
                ->constrained('attribute_groups')
                ->nullOnDelete();

            $table->string('key')->unique(); // e.g. color, size, weight
            $table->string('label');         // e.g. Color, Size
            $table->string('type', 32);      // text|number|select|multiselect|boolean|textarea|date

            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->json('meta')->nullable(); // e.g. {"min":0,"max":100,"placeholder":"..."}

            $table->timestamps();

            $table->index(['attribute_group_id', 'sort_order']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
