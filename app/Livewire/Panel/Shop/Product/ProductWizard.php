<?php

namespace App\Livewire\Panel\Shop\Product;

use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\ProductImage;
use App\Models\Shop\Unit;
use App\Support\FaterProductFetcher;
use App\Support\GigabyteProductFetcher;
use App\Support\SetareganProductFetcher;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductWizard extends Component
{
    public int $step = 1;

    // Step 1: Site selection and basic info
    public string $site_type = '';
    public string $url = '';
    public ?int $category_id = null;
    public ?int $brand_id = null;
    public string $category_search = '';
    public string $brand_search = '';

    // Step 2: Fetched product info
    public ?array $fetched_data = null;
    public bool $is_fetching = false;
    public ?string $fetch_error = null;

    // Step 2: Editable fields
    public string $name = '';
    public ?string $description = null;
    public string $slug = '';
    public string $slug_fa = '';
    public float $weight = 0;
    public float $x_dimension = 0;
    public float $y_dimension = 0;
    public float $z_dimension = 0;
    public ?int $unit_id = null;
    public string $unit_search = '';
    public array $image_urls = [];

    public function updatedName(string $value): void
    {
        $this->slug = Str::slug($value);
        $this->slug_fa = str_replace(' ', '-', $value);
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'site_type' => ['required', 'string', 'in:fater,gigabyte,setaregan'],
                'url' => ['required', 'url'],
                'category_id' => ['required', 'integer', 'exists:categories,id'],
                'brand_id' => ['required', 'integer', 'exists:brands,id'],
            ], [], [
                'site_type' => __('app.site_type'),
                'url' => __('app.product_wizard_url'),
                'category_id' => __('app.category'),
                'brand_id' => __('app.brand'),
            ]);

            // Validate URL matches site type
            if ($this->site_type === 'fater' && !str_contains($this->url, 'faterco.ir')) {
                $this->addError('url', __('app.url_does_not_match_site_type'));
                return;
            }
            if ($this->site_type === 'gigabyte' && !str_contains($this->url, 'gigabyte.com')) {
                $this->addError('url', __('app.url_does_not_match_site_type'));
                return;
            }
            if ($this->site_type === 'setaregan' && !str_contains($this->url, 'setaregan.co')) {
                $this->addError('url', __('app.url_does_not_match_site_type'));
                return;
            }

            $this->fetchProductInfo();
        }
    }

    public function fetchProductInfo(): void
    {
        $this->is_fetching = true;
        $this->fetch_error = null;
        $this->fetched_data = null;

        try {
            $logger = Log::channel('single');

            if ($this->site_type === 'fater') {
                $data = FaterProductFetcher::fetchProductInfo($this->url, $logger);
            } elseif ($this->site_type === 'gigabyte') {
                $data = GigabyteProductFetcher::fetchProductInfo($this->url, $logger);
            } elseif ($this->site_type === 'setaregan') {
                $data = SetareganProductFetcher::fetchProductInfo($this->url, $logger);
            } else {
                $this->fetch_error = __('app.unsupported_site');
                $this->is_fetching = false;
                return;
            }

            if (!$data) {
                $this->fetch_error = __('app.product_info_fetch_failed');
                $this->is_fetching = false;
                return;
            }

            $this->fetched_data = $data;

            // Populate editable fields
            $this->name = $data['name'] ?? '';
            $this->description = $data['description'] ?? null;
            $this->slug = $data['slug'] ?? Str::slug($this->name);
            $this->slug_fa = $data['slug_fa'] ?? str_replace(' ', '-', $this->name);
            $this->weight = $data['weight'] ?? 0;
            $this->x_dimension = $data['x_dimension'] ?? 0;
            $this->y_dimension = $data['y_dimension'] ?? 0;
            $this->z_dimension = $data['z_dimension'] ?? 0;
            $this->image_urls = $data['images'] ?? [];

            $this->step = 2;
            $this->is_fetching = false;
            Flux::toast(variant: 'success', text: __('app.product_info_fetched'));
        } catch (\Exception $e) {
            $this->fetch_error = __('app.product_info_fetch_failed') . ': ' . $e->getMessage();
            $this->is_fetching = false;
            Log::error('Product wizard fetch error: ' . $e->getMessage());
        }
    }

    public function back(): void
    {
        if ($this->step > 1) {
            $this->step--;
            if ($this->step === 1) {
                $this->reset(['fetched_data', 'name', 'description', 'slug', 'slug_fa', 'weight', 'x_dimension', 'y_dimension', 'z_dimension', 'image_urls', 'fetch_error']);
            }
        }
    }

    public function createProduct(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:products,slug'],
            'slug_fa' => ['required', 'string', 'max:255', 'unique:products,slug_fa'],
            'weight' => ['required', 'numeric', 'min:0'],
            'x_dimension' => ['required', 'numeric', 'min:0'],
            'y_dimension' => ['required', 'numeric', 'min:0'],
            'z_dimension' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
        ], [], [
            'name' => __('app.name'),
            'slug' => __('app.slug'),
            'slug_fa' => __('app.slug_fa'),
            'weight' => __('app.weight'),
            'x_dimension' => __('app.x_dimension'),
            'y_dimension' => __('app.y_dimension'),
            'z_dimension' => __('app.z_dimension'),
            'category_id' => __('app.category'),
            'brand_id' => __('app.brand'),
            'unit_id' => __('app.unit'),
        ]);

        // Download and save first image as product file
        $filePath = null;
        $fileName = null;
        if (!empty($this->image_urls)) {
            try {
                $firstImageUrl = $this->image_urls[0];
                $imageResponse = Http::timeout(30)->get($firstImageUrl);

                if ($imageResponse->successful()) {
                    $extension = pathinfo(parse_url($firstImageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
                    if (empty($extension)) {
                        $extension = 'jpg';
                    }
                    $fileName = Str::slug($this->name) . '.' . $extension;
                    $filePath = 'products/' . $fileName;

                    Storage::disk('public')->put($filePath, $imageResponse->body());
                }
            } catch (\Exception $e) {
                Log::warning('Failed to download product image: ' . $e->getMessage());
            }
        }

        // Create product
        $product = Product::create([
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'slug_fa' => $this->slug_fa,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'weight' => $this->weight,
            'x_dimension' => $this->x_dimension,
            'y_dimension' => $this->y_dimension,
            'z_dimension' => $this->z_dimension,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'unit_id' => $this->unit_id,
        ]);

        // Download and save additional images
        if (!empty($this->image_urls) && count($this->image_urls) > 1) {
            foreach (array_slice($this->image_urls, 1) as $imageUrl) {
                try {
                    $imageResponse = Http::timeout(30)->get($imageUrl);

                    if ($imageResponse->successful()) {
                        $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
                        if (empty($extension)) {
                            $extension = 'jpg';
                        }
                        $imageFileName = Str::slug($this->name) . '-' . uniqid() . '.' . $extension;
                        $imageFilePath = 'products/images/' . $imageFileName;

                        Storage::disk('public')->put($imageFilePath, $imageResponse->body());

                        ProductImage::create([
                            'product_id' => $product->id,
                            'file_path' => $imageFilePath,
                            'file_name' => $imageFileName,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to download product image: ' . $e->getMessage());
                }
            }
        }

        Flux::modal('panel.shop.product.product-wizard.modal')->close();
        $this->dispatch('shop.product.index.render');
        Flux::toast(variant: 'success', text: __('app.product_created_with_price_fetcher'));

        // Reset all fields
        $this->reset();
        $this->step = 1;
    }

    #[On('panel.shop.product.category.refresh')]
    public function refreshCategory($id): void
    {
        $this->category_id = $id['id'];
        $this->category_search = '';
    }

    #[On('panel.shop.product.brand.refresh')]
    public function refreshBrand($id): void
    {
        $this->brand_id = $id['id'];
        $this->brand_search = '';
    }

    #[On('panel.shop.product.unit.refresh')]
    public function refreshUnit($id): void
    {
        $this->unit_id = $id['id'];
        $this->unit_search = '';
    }

    #[Computed]
    public function categories()
    {
        $query = Category::query()
            ->where('main_category_id', '!=', 0)
            ->when($this->category_search, fn ($query) => $query->where('name', 'like', '%'.$this->category_search.'%'))
            ->with('main_category')
            ->orderBy('name')
            ->limit(20);

        $categories = $query->get(['id', 'name', 'main_category_id']);

        if ($this->category_id && !$categories->contains('id', $this->category_id)) {
            $selectedCategory = Category::with('main_category')
                ->where('id', $this->category_id)
                ->where('main_category_id', '!=', 0)
                ->first(['id', 'name', 'main_category_id']);

            if ($selectedCategory) {
                $categories->prepend($selectedCategory);
            }
        }

        return $categories;
    }

    #[Computed]
    public function brands()
    {
        $query = Brand::query()
            ->when($this->brand_search, fn ($query) => $query->where('name', 'like', '%'.$this->brand_search.'%'))
            ->orderBy('name')
            ->limit(20);

        $brands = $query->get(['id', 'name']);

        if ($this->brand_id && !$brands->contains('id', $this->brand_id)) {
            $selectedBrand = Brand::where('id', $this->brand_id)->first(['id', 'name']);

            if ($selectedBrand) {
                $brands->prepend($selectedBrand);
            }
        }

        return $brands;
    }

    #[Computed]
    public function units()
    {
        $query = Unit::query()
            ->when($this->unit_search, fn ($query) => $query->where('name', 'like', '%'.$this->unit_search.'%'))
            ->orderBy('name')
            ->limit(20);

        $units = $query->get(['id', 'name']);

        if ($this->unit_id && !$units->contains('id', $this->unit_id)) {
            $selectedUnit = Unit::where('id', $this->unit_id)->first(['id', 'name']);

            if ($selectedUnit) {
                $units->prepend($selectedUnit);
            }
        }

        return $units;
    }

    public function closeModal(): void
    {
        Flux::modal('panel.shop.product.product-wizard.modal')->close();
    }

    public function render(): View
    {
        return view('livewire.panel.shop.product.product-wizard');
    }
}
