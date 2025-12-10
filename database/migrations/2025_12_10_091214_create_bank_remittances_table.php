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
        Schema::create('bank_remittances', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->string('status')->default('pending');
            $table->bigInteger('bank_id');
            $table->bigInteger('user_id')->nullable();
            $table->decimal('draft_amount', 18, 5);
            $table->decimal('final_amount', 18, 5);
            $table->dateTime('checked_at')->nullable();
            $table->dateTime('transfer_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_remittances');
    }
};
