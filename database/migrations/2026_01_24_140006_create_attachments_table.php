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
        Schema::create('kanban_attachments', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('card_id');

            $table->bigInteger('uploaded_by')->nullable();

            // Storage target (s3/minio/public/local...)
            $table->string('disk')->default('public');
            $table->string('path'); // مسیر/کلید فایل در storage

            // File metadata
            $table->string('filename');
            $table->string('original_name')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();

            // Optional: for CDN / signed access / checksum
            $table->string('checksum')->nullable(); // sha256/md5
            $table->string('url')->nullable(); // اگر لینک خارجی یا CDN ذخیره می‌کنی

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('card_id');
            $table->index('uploaded_by');
            $table->index(['disk', 'path']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
