<?php

namespace App\Services\Shop;

use App\Models\Shop\Product;
use App\Settings\GeneralSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductImageSeoService
{
    public const WEBP_QUALITY = 80;

    private ImageManager $manager;

    public function __construct(private GeneralSettings $settings)
    {
        $this->manager = new ImageManager(new Driver);
    }

    /**
     * @param  Product|array{slug_fa?: ?string, slug?: ?string, name?: ?string}  $source
     */
    public function buildBaseStem(Product|array $source): string
    {
        if ($source instanceof Product) {
            $slugFa = $source->slug_fa;
            $slug = $source->slug;
            $name = $source->name;
        } else {
            $slugFa = $source['slug_fa'] ?? null;
            $slug = $source['slug'] ?? null;
            $name = $source['name'] ?? null;
        }

        $productPart = $this->sanitizeSegment(
            filled($slugFa) ? (string) $slugFa : (filled($slug) ? (string) $slug : slug_fa((string) $name))
        );

        $titlePart = $this->sanitizeSegment(
            slug_fa((string) ($this->settings->title ?: config('app.name')))
        );

        $hostPart = $this->sanitizeSegment($this->siteHost());

        return collect([$productPart, $titlePart, $hostPart])
            ->filter()
            ->implode('-');
    }

    public function sanitizeSegment(string $value): string
    {
        $value = str_replace([' ', '_'], '-', $value);
        $value = preg_replace('/[^\p{L}\p{N}\-]+/u', '-', $value) ?? '';
        $value = preg_replace('/-+/', '-', $value) ?? '';

        return trim($value, '-');
    }

    public function siteHost(): string
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';

        return str_replace('.', '-', strtolower($host));
    }

    public function siteTitle(): string
    {
        return (string) ($this->settings->title ?: config('app.name'));
    }

    public function imageAlt(Product|string|null $productOrName): string
    {
        $name = is_object($productOrName)
            ? (string) ($productOrName->name ?? '')
            : (string) $productOrName;

        $site = $this->siteTitle();

        return filled($name) ? "{$name} | {$site}" : $site;
    }

    public function isMarkedPath(string $path): bool
    {
        return str_ends_with(
            pathinfo($path, PATHINFO_FILENAME),
            ImageWatermarkService::MARK_SUFFIX
        );
    }

    /**
     * @param  Product|array{slug_fa?: ?string, slug?: ?string, name?: ?string}  $source
     */
    public function isOptimized(string $filePath, Product|array $source): bool
    {
        if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'webp') {
            return false;
        }

        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        $stem = $filename;

        if (str_ends_with($stem, ImageWatermarkService::MARK_SUFFIX)) {
            $stem = substr($stem, 0, -strlen(ImageWatermarkService::MARK_SUFFIX));
        }

        $base = $this->buildBaseStem($source);

        if ($stem === $base) {
            return true;
        }

        return (bool) preg_match('/^'.preg_quote($base, '/').'-\d+$/u', $stem);
    }

    /**
     * @param  Product|array{slug_fa?: ?string, slug?: ?string, name?: ?string}  $source
     * @return array{file_path: string, file_name: string}
     */
    public function uniqueRelativePath(
        string $directory,
        Product|array $source,
        bool $marked = false,
        ?string $excludePath = null,
    ): array {
        $directory = trim($directory, '/');
        $base = $this->buildBaseStem($source);
        $suffix = $marked ? ImageWatermarkService::MARK_SUFFIX : '';
        $index = 0;

        do {
            $fileName = $index === 0
                ? $base.$suffix.'.webp'
                : $base.'-'.$index.$suffix.'.webp';
            $filePath = $directory.'/'.$fileName;
            $index++;
        } while (
            Storage::disk('public')->exists($filePath)
            && ($excludePath === null || $filePath !== $excludePath)
        );

        return [
            'file_path' => $filePath,
            'file_name' => $fileName,
        ];
    }

    /**
     * Convert binary/path/upload to SEO-named WebP and store on the public disk.
     *
     * @param  Product|array{slug_fa?: ?string, slug?: ?string, name?: ?string}  $source
     * @return array{file_path: string, file_name: string}
     */
    public function storeAsWebp(
        string|UploadedFile|TemporaryUploadedFile $sourceFile,
        string $directory,
        Product|array $source,
        ?string $oldPath = null,
        bool $preserveMarked = true,
    ): array {
        $marked = $preserveMarked && $oldPath !== null && $this->isMarkedPath($oldPath);
        $paths = $this->uniqueRelativePath($directory, $source, $marked, $oldPath);

        $binary = $this->readBinary($sourceFile);
        $encoded = (string) $this->manager->read($binary)->toWebp(quality: self::WEBP_QUALITY);

        if (! Storage::disk('public')->put($paths['file_path'], $encoded)) {
            throw new \RuntimeException('Failed to store optimized image: '.$paths['file_path']);
        }

        if ($oldPath !== null && $oldPath !== $paths['file_path'] && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return $paths;
    }

    /**
     * Re-encode and rename an existing disk-relative image.
     *
     * @return array{file_path: string, file_name: string}
     */
    public function optimizeExisting(string $relativePath, Product $product): array
    {
        if (! Storage::disk('public')->exists($relativePath)) {
            throw new \RuntimeException("Source image missing: {$relativePath}");
        }

        $directory = pathinfo($relativePath, PATHINFO_DIRNAME);
        if ($directory === '.' || $directory === '') {
            $directory = 'products';
        }

        return $this->storeAsWebp(
            Storage::disk('public')->path($relativePath),
            $directory,
            $product,
            $relativePath,
            preserveMarked: true,
        );
    }

    private function readBinary(string|UploadedFile|TemporaryUploadedFile $sourceFile): string
    {
        if ($sourceFile instanceof UploadedFile || $sourceFile instanceof TemporaryUploadedFile) {
            $contents = file_get_contents($sourceFile->getRealPath());

            if ($contents === false) {
                throw new \RuntimeException('Unable to read uploaded image.');
            }

            return $contents;
        }

        if (is_file($sourceFile)) {
            $contents = file_get_contents($sourceFile);

            if ($contents === false) {
                throw new \RuntimeException("Unable to read image: {$sourceFile}");
            }

            return $contents;
        }

        return $sourceFile;
    }
}
