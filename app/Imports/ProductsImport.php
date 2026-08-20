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
use Maatwebsite\Excel\Facades\Excel;

class ProductsImport implements SkipsEmptyRows, ToModel, WithStartRow
{
    public int $created = 0;

    public int $updated = 0;

    public int $unchanged = 0;

    public int $skipped = 0;

    /** @var array<int, string> */
    public array $errors = [];

    protected int $rowNumber = 1;

    public function __construct(
        protected ProductImageSeoService $seo,
        protected string $format = 'template',
    ) {}

    public static function detectFormat(string $path): string
    {
        $rows = Excel::toArray([], $path);
        $firstHeader = strtolower(trim((string) ($rows[0][0][0] ?? '')));

        if (in_array($firstHeader, ['id', 'شناسه'], true)) {
            return 'export';
        }

        return 'template';
    }

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

        if ($this->format === 'export') {
            return $this->processExportRow($row);
        }

        return $this->processTemplateRow($row);
    }

    /**
     * @param  array<int, mixed>  $row
     */
    protected function processExportRow(array $row): ?Product
    {
        $productId = $this->parseId($row[0] ?? null);
        $name = trim((string) ($row[1] ?? ''));

        if ($name !== '' && $this->isHeaderLike($name)) {
            return null;
        }

        $data = [
            'name' => $name,
            'en_name' => trim((string) ($row[2] ?? '')),
            'category_name' => trim((string) ($row[3] ?? '')),
            'brand_name' => trim((string) ($row[4] ?? '')),
            'unit_name' => trim((string) ($row[5] ?? '')),
            'price' => $this->parseNumber($row[6] ?? null),
            'sale_price' => $this->parseNumber($row[7] ?? null),
            'quantity' => $this->parseNumber($row[8] ?? null),
            'description' => trim((string) ($row[9] ?? '')),
            'weight' => $this->parseNumber($row[10] ?? null) ?? 0,
            'image_url' => trim((string) ($row[11] ?? '')),
        ];

        if ($productId !== null) {
            $product = Product::query()->find($productId);

            if ($product === null) {
                $this->skipped++;
                $this->errors[] = __('general.import_row_skipped', [
                    'row' => $this->rowNumber,
                    'name' => $name !== '' ? $name : (string) $productId,
                ]);

                return null;
            }

            if ($this->updateProduct($product, $data)) {
                $this->updated++;
            } else {
                $this->unchanged++;
            }

            return null;
        }

        if ($name === '') {
            return null;
        }

        return $this->createProduct($data);
    }

    /**
     * @param  array<int, mixed>  $row
     */
    protected function processTemplateRow(array $row): ?Product
    {
        $name = trim((string) ($row[0] ?? ''));

        if ($name === '' || $this->isHeaderLike($name)) {
            return null;
        }

        $data = [
            'name' => $name,
            'en_name' => trim((string) ($row[1] ?? '')),
            'description' => trim((string) ($row[2] ?? '')),
            'category_name' => trim((string) ($row[3] ?? '')),
            'brand_name' => trim((string) ($row[4] ?? '')),
            'unit_name' => trim((string) ($row[5] ?? '')),
            'price' => $this->parseNumber($row[6] ?? null),
            'sale_price' => $this->parseNumber($row[7] ?? null),
            'quantity' => $this->parseNumber($row[8] ?? null),
            'weight' => $this->parseNumber($row[9] ?? null) ?? 0,
            'image_url' => trim((string) ($row[10] ?? '')),
        ];

        return $this->createProduct($data);
    }

    /**
     * @param  array{
     *     name: string,
     *     en_name: string,
     *     description?: string,
     *     category_name: string,
     *     brand_name: string,
     *     unit_name: string,
     *     price: ?float,
     *     sale_price: ?float,
     *     quantity: ?float,
     *     weight?: float,
     *     image_url?: string
     * }  $data
     */
    protected function createProduct(array $data): ?Product
    {
        $categoryId = $this->resolveCategoryId($data['category_name']);
        $brandId = Brand::query()->where('name', $data['brand_name'])->value('id');
        $unitId = Unit::query()->where('name', $data['unit_name'])->value('id');

        if ($categoryId === null || $brandId === null || $unitId === null) {
            $this->skipped++;
            $this->errors[] = __('general.import_row_skipped', [
                'row' => $this->rowNumber,
                'name' => $data['name'],
            ]);

            return null;
        }

        [$slug, $slugFa] = $this->uniqueSlugs($data['name']);

        $naming = [
            'slug_fa' => $slugFa,
            'slug' => $slug,
            'name' => $data['name'],
        ];

        [$filePath, $fileName] = $this->resolveImage($data['image_url'] ?? '', $naming, $data['name']);

        $product = Product::create([
            'name' => $data['name'],
            'en_name' => $data['en_name'] !== '' ? $data['en_name'] : null,
            'description' => ($data['description'] ?? '') !== '' ? $data['description'] : null,
            'slug' => $slug,
            'slug_fa' => $slugFa,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'weight' => $data['weight'] ?? 0,
            'x_dimension' => 0,
            'y_dimension' => 0,
            'z_dimension' => 0,
            'category_id' => $categoryId,
            'brand_id' => $brandId,
            'unit_id' => $unitId,
        ]);

        if ($data['price'] !== null) {
            ProductPrice::create([
                'product_id' => $product->id,
                'price' => $data['price'],
                'sale_price' => $data['sale_price'],
                'quantity' => $data['quantity'] ?? 0,
                'is_default' => true,
            ]);
        }

        $this->created++;

        return $product;
    }

    /**
     * @param  array{
     *     name: string,
     *     en_name: string,
     *     category_name: string,
     *     brand_name: string,
     *     unit_name: string,
     *     price: ?float,
     *     sale_price: ?float,
     *     quantity: ?float
     * }  $data
     */
    protected function updateProduct(Product $product, array $data): bool
    {
        $changed = false;
        $updates = [];

        if ($data['name'] !== '' && $data['name'] !== $product->name) {
            $updates['name'] = $data['name'];
            [$slug, $slugFa] = $this->uniqueSlugs($data['name'], $product->id);
            $updates['slug'] = $slug;
            $updates['slug_fa'] = $slugFa;
        }

        if ($data['en_name'] !== '' && $data['en_name'] !== ($product->en_name ?? '')) {
            $updates['en_name'] = $data['en_name'];
        }

        if ($data['category_name'] !== '') {
            $categoryId = $this->resolveCategoryId($data['category_name']);

            if ($categoryId !== null && $categoryId !== (int) $product->category_id) {
                $updates['category_id'] = $categoryId;
            }
        }

        if ($data['brand_name'] !== '') {
            $brandId = Brand::query()->where('name', $data['brand_name'])->value('id');

            if ($brandId !== null && (int) $brandId !== (int) $product->brand_id) {
                $updates['brand_id'] = $brandId;
            }
        }

        if ($data['unit_name'] !== '') {
            $unitId = Unit::query()->where('name', $data['unit_name'])->value('id');

            if ($unitId !== null && (int) $unitId !== (int) $product->unit_id) {
                $updates['unit_id'] = $unitId;
            }
        }

        if ($updates !== []) {
            $product->update($updates);
            $changed = true;
        }

        if ($this->syncDefaultPrice($product, $data)) {
            $changed = true;
        }

        return $changed;
    }

    /**
     * @param  array{price: ?float, sale_price: ?float, quantity: ?float}  $data
     */
    protected function syncDefaultPrice(Product $product, array $data): bool
    {
        if ($data['price'] === null && $data['sale_price'] === null && $data['quantity'] === null) {
            return false;
        }

        $default = $product->default_price;
        $record = $default['record'] ?? null;

        if ($record === null) {
            if ($data['price'] === null) {
                return false;
            }

            ProductPrice::create([
                'product_id' => $product->id,
                'price' => $data['price'],
                'sale_price' => $data['sale_price'],
                'quantity' => $data['quantity'] ?? 0,
                'is_default' => true,
            ]);

            return true;
        }

        $updates = [];

        if ($data['price'] !== null && (float) $record->price !== (float) $data['price']) {
            $updates['price'] = $data['price'];
        }

        if ($data['sale_price'] !== null && (float) ($record->sale_price ?? 0) !== (float) $data['sale_price']) {
            $updates['sale_price'] = $data['sale_price'];
        }

        if ($data['quantity'] !== null && (float) $record->quantity !== (float) $data['quantity']) {
            $updates['quantity'] = $data['quantity'];
        }

        if ($updates === []) {
            return false;
        }

        $record->update($updates);

        return true;
    }

    protected function parseId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = trim((string) $value);

        if ($normalized === '' || ! is_numeric($normalized)) {
            return null;
        }

        return (int) $normalized;
    }

    protected function isHeaderLike(string $value): bool
    {
        $needles = [
            __('general.name'),
            __('general.id'),
            'name',
            'id',
            'نام',
            'شناسه',
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
    protected function uniqueSlugs(string $name, ?int $exceptProductId = null): array
    {
        $baseSlug = Str::slug($name) ?: 'product';
        $baseSlugFa = slug_fa($name) ?: 'product';
        $slug = $baseSlug;
        $slugFa = $baseSlugFa;
        $counter = 1;

        while (
            Product::query()
                ->when($exceptProductId !== null, fn ($query) => $query->where('id', '!=', $exceptProductId))
                ->where(function ($query) use ($slug, $slugFa): void {
                    $query->where('slug', $slug)->orWhere('slug_fa', $slugFa);
                })
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
