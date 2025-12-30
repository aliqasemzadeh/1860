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
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable()->comment('نام آدرس (اولین آدرس ممکنه اسم خاصی نداشته باشه)');
            $table->integer('province_id')->comment('شناسه استان');
            $table->integer('city_id')->comment('شناسه شهر');
            $table->text('address')->comment('آدرس کامل');
            $table->string('postal_code', 10)->nullable()->comment('کد پستی');
            $table->decimal('latitude', 10, 8)->nullable()->comment('عرض جغرافیایی');
            $table->decimal('longitude', 11, 8)->nullable()->comment('طول جغرافیایی');
            $table->string('emergency_contact', 20)->nullable()->comment('شماره تماس ضروری');
            $table->boolean('is_default')->default(false)->comment('آدرس پیش‌فرض');
            $table->timestamps();
            
            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_addresses');
    }
};
