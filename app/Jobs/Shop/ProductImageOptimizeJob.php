<?php

namespace App\Jobs\Shop;

use App\Models\Shop\ProductImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class ProductImageOptimizeJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $productImageId
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $productImage = ProductImage::find($this->productImageId);

        if (! $productImage) {
            Log::warning("ProductImage not found for optimization", ['id' => $this->productImageId]);
            return;
        }

        $filePath = $productImage->file_path;
        $disk = Storage::disk('public');

        if (! $disk->exists($filePath)) {
            Log::warning("File not found for optimization", ['path' => $filePath, 'id' => $this->productImageId]);
            return;
        }

        try {
            // خواندن فایل اصلی
            $originalContent = $disk->get($filePath);
            
            // دریافت extension فایل اصلی
            $originalExtension = pathinfo($productImage->file_name, PATHINFO_EXTENSION);
            if (empty($originalExtension)) {
                $originalExtension = 'jpg'; // پیش‌فرض
            }
            
            // ایجاد فایل موقت برای پردازش
            $tempDir = sys_get_temp_dir();
            $tempInputPath = $tempDir . DIRECTORY_SEPARATOR . uniqid('img_', true) . '.' . $originalExtension;
            $tempOutputPath = $tempDir . DIRECTORY_SEPARATOR . uniqid('img_', true) . '.png';

            // ذخیره فایل موقت
            file_put_contents($tempInputPath, $originalContent);

            // پیدا کردن مسیر removebg.js (نسبت به root پروژه)
            $removeBgScriptPath = base_path('removebg.js');

            if (! file_exists($removeBgScriptPath)) {
                Log::error("removebg.js not found", ['path' => $removeBgScriptPath]);
                return;
            }

            // اجرای removebg.js
            $process = new Process([
                'node',
                $removeBgScriptPath,
                $tempInputPath,
                $tempOutputPath,
            ]);

            $process->setTimeout(300); // 5 minutes timeout
            $process->run();

            if (! $process->isSuccessful()) {
                Log::error("Background removal failed", [
                    'error' => $process->getErrorOutput(),
                    'output' => $process->getOutput(),
                    'product_image_id' => $this->productImageId,
                ]);
                
                // پاک کردن فایل‌های موقت
                @unlink($tempInputPath);
                @unlink($tempOutputPath);
                return;
            }

            // خواندن فایل PNG بهینه‌شده
            if (! file_exists($tempOutputPath)) {
                Log::error("Optimized file not created", ['path' => $tempOutputPath]);
                @unlink($tempInputPath);
                return;
            }

            $optimizedContent = file_get_contents($tempOutputPath);

            // تبدیل extension به .png
            $newFileName = pathinfo($productImage->file_name, PATHINFO_FILENAME) . '.png';
            $newFilePath = pathinfo($filePath, PATHINFO_DIRNAME) . '/' . $newFileName;

            // اگر فایل با نام جدید وجود دارد، حذفش کن
            if ($disk->exists($newFilePath) && $newFilePath !== $filePath) {
                $disk->delete($newFilePath);
            }

            // ذخیره فایل PNG جدید
            $saved = $disk->put($newFilePath, $optimizedContent);

            if ($saved) {
                // اگر مسیر جدید متفاوت از مسیر قبلی است، فایل قدیمی را حذف کن
                if ($newFilePath !== $filePath && $disk->exists($filePath)) {
                    $disk->delete($filePath);
                }

                // آپدیت رکورد در دیتابیس
                $productImage->update([
                    'file_path' => $newFilePath,
                    'file_name' => $newFileName,
                ]);

                Log::info("Image optimized successfully", [
                    'product_image_id' => $this->productImageId,
                    'old_path' => $filePath,
                    'new_path' => $newFilePath,
                ]);
            } else {
                Log::error("Failed to save optimized image", [
                    'product_image_id' => $this->productImageId,
                    'path' => $newFilePath,
                ]);
            }

            // پاک کردن فایل‌های موقت
            @unlink($tempInputPath);
            @unlink($tempOutputPath);
        } catch (\Exception $e) {
            Log::error("Exception during image optimization", [
                'product_image_id' => $this->productImageId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
