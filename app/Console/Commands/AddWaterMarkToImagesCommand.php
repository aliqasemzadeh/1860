<?php

namespace App\Console\Commands;

use App\Models\Shop\Product;
use App\Models\Shop\ProductImage;
use App\Services\Shop\ImageWatermarkService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AddWaterMarkToImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-water-mark-to-images-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add the site logo as a top-left watermark to product images not yet marked.';

    /**
     * Execute the console command.
     */
    public function handle(ImageWatermarkService $watermark): int
    {
        if (! $watermark->hasLogo()) {
            $this->error('No site logo configured (GeneralSettings::logo_path) — aborting.');
            $this->line('RESULT products_marked=0 products_skipped=0 product_images_marked=0 product_images_skipped=0');

            return self::FAILURE;
        }

        $productStats = ['marked' => 0, 'skipped' => 0, 'failed' => 0];
        $imageStats = ['marked' => 0, 'skipped' => 0, 'failed' => 0];

        $this->processModel(
            Product::query()->whereNotNull('file_path')->where('file_path', '!=', ''),
            'products',
            $watermark,
            $productStats,
        );

        $this->processModel(
            ProductImage::query()->whereNotNull('file_path')->where('file_path', '!=', ''),
            'product_images',
            $watermark,
            $imageStats,
        );

        $this->table(['Type', 'Marked', 'Skipped', 'Failed'], [
            ['Products', $productStats['marked'], $productStats['skipped'], $productStats['failed']],
            ['Product Images', $imageStats['marked'], $imageStats['skipped'], $imageStats['failed']],
        ]);

        $this->line(sprintf(
            'RESULT products_marked=%d products_skipped=%d product_images_marked=%d product_images_skipped=%d',
            $productStats['marked'],
            $productStats['skipped'],
            $imageStats['marked'],
            $imageStats['skipped'],
        ));

        return self::SUCCESS;
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array{marked: int, skipped: int, failed: int}  $stats
     */
    private function processModel(
        Builder $query,
        string $label,
        ImageWatermarkService $watermark,
        array &$stats,
    ): void {
        $bar = $this->output->createProgressBar((clone $query)->count());
        $bar->setFormat("%message%\n %current%/%max% [%bar%] %percent:3s%%");
        $bar->setMessage("Processing {$label}");
        $bar->start();

        $query->chunkById(100, function ($records) use ($watermark, $label, &$stats, $bar): void {
            foreach ($records as $record) {
                $bar->advance();

                $oldPath = (string) $record->file_path;

                if ($watermark->isMarked($oldPath) || ! Storage::disk('public')->exists($oldPath)) {
                    $stats['skipped']++;

                    continue;
                }

                try {
                    $newFilePath = $watermark->apply($oldPath);
                    $newFileName = filled($record->file_name)
                        ? $watermark->markedPath((string) $record->file_name)
                        : $record->file_name;

                    $record->forceFill([
                        'file_path' => $newFilePath,
                        'file_name' => $newFileName,
                    ])->save();

                    if ($oldPath !== $newFilePath) {
                        Storage::disk('public')->delete($oldPath);
                    }

                    $stats['marked']++;
                } catch (\Throwable $e) {
                    Log::error("Watermark failed for {$label} #{$record->id}: {$e->getMessage()}");
                    $stats['failed']++;
                }
            }
        });

        $bar->finish();
        $this->newLine(2);
    }
}
