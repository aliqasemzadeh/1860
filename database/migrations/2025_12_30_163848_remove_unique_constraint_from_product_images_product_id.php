<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove unique constraint on product_id using raw SQL
        // This works for both MySQL and other databases
        try {
            DB::statement('ALTER TABLE `product_images` DROP INDEX `product_images_product_id_unique`');
        } catch (\Exception $e) {
            // Constraint might not exist or might have a different name
            // Try alternative method
            try {
                Schema::table('product_images', function (Blueprint $table) {
                    $table->dropUnique('product_images_product_id_unique');
                });
            } catch (\Exception $e2) {
                // If both fail, the constraint might not exist or have a different name
                // Continue without error
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            // Re-add unique constraint if needed (though this shouldn't be necessary)
            $table->unique('product_id', 'product_images_product_id_unique');
        });
    }
};
