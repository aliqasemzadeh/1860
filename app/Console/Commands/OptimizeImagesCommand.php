<?php

namespace App\Console\Commands;

use App\Models\Shop\Product;
use App\Models\Shop\ProductImage;
use App\Services\Shop\ProductImageSeoService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OptimizeImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize-images-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert product images to WebP and rename them with SEO-friendly filenames.';

    /**
     * Execute the console command.
     */
    public function handle(ProductImageSeoService $seo): int
    {
        $productStats = ['optimized' => 0, 'skipped' => 0, 'failed' => 0];
        $imageStats = ['optimized' => 0, 'skipped' => 0, 'failed' => 0];

        $this->processModel(
            Product::query()->whereNotNull('file_path')->where('file_path', '!=', ''),
            'products',
            $seo,
            $productStats,
            fn (Product $product): Product => $product,
        );

        $this->processModel(
            ProductImage::query()
                ->whereNotNull('file_path')
                ->where('file_path', '!=', '')
                ->with('product'),
            'product_images',
            $seo,
            $imageStats,
            fn (ProductImage $image): ?Product => $image->product,
        );

        $this->table(['Type', 'Optimized', 'Skipped', 'Failed'], [
            ['Products', $productStats['optimized'], $productStats['skipped'], $productStats['failed']],
            ['Product Images', $imageStats['optimized'], $imageStats['skipped'], $imageStats['failed']],
        ]);

        $this->line(sprintf(
            'RESULT products_optimized=%d products_skipped=%d product_images_optimized=%d product_images_skipped=%d',
            $productStats['optimized'],
            $productStats['skipped'],
            $imageStats['optimized'],
            $imageStats['skipped'],
        ));

        return self::SUCCESS;
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array{optimized: int, skipped: int, failed: int}  $stats
     * @param  callable(Model): (?Product)  $productResolver
     */
    private function processModel(
        Builder $query,
        string $label,
        ProductImageSeoService $seo,
        array &$stats,
        callable $productResolver,
    ): void {
        $bar = $this->output->createProgressBar((clone $query)->count());
        $bar->setFormat("%message%\n %current%/%max% [%bar%] %percent:3s%%");
        $bar->setMessage("Processing {$label}");
        $bar->start();

        $query->chunkById(100, function ($records) use ($seo, $label, &$stats, $bar, $productResolver): void {
            foreach ($records as $record) {
                $bar->advance();

                $oldPath = (string) $record->file_path;
                $product = $productResolver($record);

                if ($product === null || ! Storage::disk('public')->exists($oldPath)) {
                    $stats['skipped']++;

                    continue;
                }

                if ($seo->isOptimized($oldPath, $product)) {
                    $stats['skipped']++;

                    continue;
                }

                try {
                    $paths = $seo->optimizeExisting($oldPath, $product);

                    $record->forceFill([
                        'file_path' => $paths['file_path'],
                        'file_name' => $paths['file_name'],
                    ])->save();

                    $stats['optimized']++;
                } catch (\Throwable $e) {
                    Log::error("Image optimize failed for {$label} #{$record->id}: {$e->getMessage()}");
                    $stats['failed']++;
                }
            }
        });

        $bar->finish();
        $this->newLine(2);
    }
}
