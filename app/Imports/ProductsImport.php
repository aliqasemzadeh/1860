<?php

namespace App\Imports;

use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\ProductPrice;
use App\Models\Shop\Unit;
use App\Services\Shop\ProductImageSeoService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ProductsImport implements SkipsEmptyRows, ToModel, WithStartRow
{
    public int $imported = 0;

    public int $skipped = 0;

    /** @var array<int, string> */
    public array $errors = [];

    protected int $rowNumber = 1;

    public function __construct(protected ProductImageSeoService $seo) {}

    public function startRow(): int
    {
        return 2;
    }

    /**
     * @param  array<int, mixed>  $row
     */
    public function model(array $row): ?Product
    {
        $this->rowNumber++;

        $name = trim((string) ($row[0] ?? ''));

        if ($name === '' || $this->isHeaderLike($name)) {
            return null;
        }

        $enName = trim((string) ($row[1] ?? ''));
        $description = trim((string) ($row[2] ?? ''));
        $categoryName = trim((string) ($row[3] ?? ''));
        $brandName = trim((string) ($row[4] ?? ''));
        $unitName = trim((string) ($row[5] ?? ''));
        $price = $this->parseNumber($row[6] ?? null);
        $salePrice = $this->parseNumber($row[7] ?? null);
        $quantity = $this->parseNumber($row[8] ?? null) ?? 0;
        $weight = $this->parseNumber($row[9] ?? null) ?? 0;
        $imageUrl = trim((string) ($row[10] ?? ''));

        $categoryId = $this->resolveCategoryId($categoryName);
        $brandId = Brand::query()->where('name', $brandName)->value('id');
        $unitId = Unit::query()->where('name', $unitName)->value('id');

        if ($categoryId === null || $brandId === null || $unitId === null) {
            $this->skipped++;
            $this->errors[] = __('general.import_row_skipped', [
                'row' => $this->rowNumber,
                'name' => $name,
            ]);

            return null;
        }

        [$slug, $slugFa] = $this->uniqueSlugs($name);

        $naming = [
            'slug_fa' => $slugFa,
            'slug' => $slug,
            'name' => $name,
        ];

        [$filePath, $fileName] = $this->resolveImage($imageUrl, $naming, $name);

        $product = Product::create([
            'name' => $name,
            'en_name' => $enName !== '' ? $enName : null,
            'description' => $description !== '' ? $description : null,
            'slug' => $slug,
            'slug_fa' => $slugFa,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'weight' => $weight,
            'x_dimension' => 0,
            'y_dimension' => 0,
            'z_dimension' => 0,
            'category_id' => $categoryId,
            'brand_id' => $brandId,
            'unit_id' => $unitId,
        ]);

        if ($price !== null) {
            ProductPrice::create([
                'product_id' => $product->id,
                'price' => $price,
                'sale_price' => $salePrice,
                'quantity' => $quantity,
                'is_default' => true,
            ]);
        }

        $this->imported++;

        return $product;
    }

    protected function isHeaderLike(string $value): bool
    {
        $needles = [
            __('general.name'),
            'name',
            'نام',
        ];

        return in_array($value, $needles, true);
    }

    protected function parseNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = str_replace([',', ' '], '', (string) $value);

        if ($normalized === '' || ! is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    protected function resolveCategoryId(string $value): ?int
    {
        if ($value === '') {
            return null;
        }

        if (str_contains($value, ' - ')) {
            [$mainName, $subName] = array_map('trim', explode(' - ', $value, 2));

            $categoryId = Category::query()
                ->where('name', $subName)
                ->where('main_category_id', '!=', 0)
                ->whereHas('main_category', fn ($query) => $query->where('name', $mainName))
                ->value('id');

            if ($categoryId !== null) {
                return (int) $categoryId;
            }
        }

        $categoryId = Category::query()
            ->where('name', $value)
            ->where('main_category_id', '!=', 0)
            ->value('id');

        return $categoryId !== null ? (int) $categoryId : null;
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function uniqueSlugs(string $name): array
    {
        $baseSlug = Str::slug($name) ?: 'product';
        $baseSlugFa = slug_fa($name) ?: 'product';
        $slug = $baseSlug;
        $slugFa = $baseSlugFa;
        $counter = 1;

        while (
            Product::query()
                ->where('slug', $slug)
                ->orWhere('slug_fa', $slugFa)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $slugFa = $baseSlugFa.'-'.$counter;
            $counter++;
        }

        return [$slug, $slugFa];
    }

    /**
     * @param  array{slug_fa: string, slug: string, name: string}  $naming
     * @return array{0: string, 1: string}
     */
    protected function resolveImage(string $imageUrl, array $naming, string $name): array
    {
        if ($imageUrl === '' || ! filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return ['', ''];
        }

        try {
            $response = Http::timeout(30)->get($imageUrl);

            if (! $response->successful()) {
                Log::warning('Product import image download failed', [
                    'product' => $name,
                    'url' => $imageUrl,
                    'status' => $response->status(),
                ]);

                return ['', ''];
            }

            $paths = $this->seo->storeAsWebp($response->body(), 'products', $naming);

            return [$paths['file_path'], $paths['file_name']];
        } catch (\Throwable $e) {
            Log::warning('Product import image download failed', [
                'product' => $name,
                'url' => $imageUrl,
                'error' => $e->getMessage(),
            ]);

            return ['', ''];
        }
    }
}
