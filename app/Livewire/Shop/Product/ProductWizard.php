<?php

namespace App\Livewire\Shop\Product;

use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\PriceFetcher;
use App\Models\Shop\Product;
use App\Models\Shop\Unit;
use App\Support\FaterProductFetcher;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductWizard extends Component
{
    use WithFileUploads;

    public string $url = '';

    public ?int $category_id = null;

    public ?int $brand_id = null;

    public ?int $unit_id = null;

    public string $category_search = '';

    public string $brand_search = '';

    public string $unit_search = '';

    // Product fields (filled after fetching)
    public string $name = '';

    public ?string $description = null;

    public string $slug = '';

    public string $slug_fa = '';

    #[Validate('nullable|file|max:10240')]
    public $file = null;

    public float $weight = 0;

    public float $x_dimension = 0;

    public float $y_dimension = 0;

    public float $z_dimension = 0;

    public bool $infoFetched = false;

    public string $detectedType = '';

    public function mount(): void
    {
        //
    }

    public function detectSiteType(string $url): ?string
    {
        if (str_contains($url, 'faterco.ir')) {
            return 'fater';
        }
        if (str_contains($url, 'digikala.com')) {
            return 'digikala';
        }
        if (str_contains($url, 'fafait.com')) {
            return 'fafait';
        }
        if (str_contains($url, 'markazi.com')) {
            return 'markazi';
        }

        return null;
    }

    public function fetchProductInfo(): void
    {
        $this->validate([
            'url' => ['required', 'url', 'max:500'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
        ], [], [
            'url' => __('app.product_wizard_url'),
            'category_id' => __('app.category'),
            'brand_id' => __('app.brand'),
        ]);

        $this->detectedType = $this->detectSiteType($this->url);

        if (!$this->detectedType) {
            Flux::toast(variant: 'danger', text: __('app.unsupported_site'));
            return;
        }

        // Fetch product info based on type
        $productInfo = match ($this->detectedType) {
            'fater' => FaterProductFetcher::fetchProductInfo($this->url, Log::channel()),
            default => [
                'name' => null,
                'description' => null,
                'slug' => null,
                'slug_fa' => null,
                'weight' => null,
                'x_dimension' => null,
                'y_dimension' => null,
                'z_dimension' => null,
            ],
        };

        // Fill form fields
        $this->name = $productInfo['name'] ?? '';
        $this->description = $productInfo['description'] ?? null;
        $this->slug = $productInfo['slug'] ?? '';
        $this->slug_fa = $productInfo['slug_fa'] ?? '';
        $this->weight = $productInfo['weight'] ?? 0;
        $this->x_dimension = $productInfo['x_dimension'] ?? 0;
        $this->y_dimension = $productInfo['y_dimension'] ?? 0;
        $this->z_dimension = $productInfo['z_dimension'] ?? 0;

        $this->infoFetched = true;

        Flux::toast(variant: 'success', text: __('app.product_info_fetched'));
    }

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:products,slug'],
            'slug_fa' => ['required', 'string', 'max:255', 'unique:products,slug_fa'],
            'file' => ['nullable', 'file', 'max:10240'],
            'weight' => ['required', 'numeric', 'min:0'],
            'x_dimension' => ['required', 'numeric', 'min:0'],
            'y_dimension' => ['required', 'numeric', 'min:0'],
            'z_dimension' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'url' => ['required', 'url', 'max:500'],
        ]);

        // Store file if provided
        $filePath = null;
        $fileName = null;
        if ($this->file) {
            $filePath = $this->file->storeAs('products', $this->file->getClientOriginalName(), 'public');
            $fileName = $this->file->getClientOriginalName();
        }

        // Create product
        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'slug' => $validated['slug'],
            'slug_fa' => $validated['slug_fa'],
            'file_path' => $filePath,
            'file_name' => $fileName,
            'weight' => $validated['weight'],
            'x_dimension' => $validated['x_dimension'],
            'y_dimension' => $validated['y_dimension'],
            'z_dimension' => $validated['z_dimension'],
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'unit_id' => $validated['unit_id'],
        ]);

        // Create price fetcher
        PriceFetcher::create([
            'product_id' => $product->id,
            'type' => $this->detectedType,
            'url' => $validated['url'],
        ]);

        Flux::modal('shop.product.product-wizard.modal')->close();
        $this->dispatch('shop.product.index.render');
        Flux::toast(variant: 'success', text: __('app.product_created_with_price_fetcher'));
        $this->reset([
            'url',
            'category_id',
            'brand_id',
            'unit_id',
            'category_search',
            'brand_search',
            'unit_search',
            'name',
            'description',
            'slug',
            'slug_fa',
            'file',
            'weight',
            'x_dimension',
            'y_dimension',
            'z_dimension',
            'infoFetched',
            'detectedType',
        ]);
    }

    public function removeFile(): void
    {
        if ($this->file) {
            $this->file->delete();
            $this->file = null;
            Flux::toast(variant: 'success', text: __('app.file_removed'));
        }
    }

    #[On('shop.product.category.refresh')]
    public function refreshCategory($id): void
    {
        $this->category_id = $id['id'];
        $this->category_search = '';
    }

    #[On('shop.product.brand.refresh')]
    public function refreshBrand($id): void
    {
        $this->brand_id = $id['id'];
        $this->brand_search = '';
    }

    #[On('shop.product.unit.refresh')]
    public function refreshUnit($id): void
    {
        $this->unit_id = $id['id'];
        $this->unit_search = '';
    }

    #[Computed]
    public function categories()
    {
        return Category::query()
            ->where('main_category_id', '!=', 0)
            ->when($this->category_search, fn($query) => $query->where('name', 'like', '%' . $this->category_search . '%'))
            ->with('main_category')
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'main_category_id']);
    }

    #[Computed]
    public function brands()
    {
        return Brand::query()
            ->when($this->brand_search, fn($query) => $query->where('name', 'like', '%' . $this->brand_search . '%'))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name']);
    }

    #[Computed]
    public function units()
    {
        return Unit::query()
            ->when($this->unit_search, fn($query) => $query->where('name', 'like', '%' . $this->unit_search . '%'))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name']);
    }

    public function render(): View
    {
        return view('livewire.shop.product.product-wizard');
    }
}
