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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('workspace_id');

            $table->string('name');
            $table->string('key', 32)->nullable(); // مثل: PAY, DEVOPS, CRM (اختیاری)
            $table->text('description')->nullable();
            $table->string('color', 32)->nullable();

            $table->json('settings')->nullable();
            $table->timestamp('archived_at')->nullable();

            $table->timestamps();

            $table->unique(['workspace_id', 'name']);
            $table->unique(['workspace_id', 'key']);

            $table->index(['workspace_id', 'archived_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
