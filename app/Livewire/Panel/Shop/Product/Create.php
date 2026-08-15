<?php

namespace App\Livewire\Panel\Shop\Product;

use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\Unit;
use App\Services\Shop\ProductImageSeoService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $en_name = '';

    public ?string $description = null;

    public string $slug = '';

    public string $slug_fa = '';

    #[Validate('nullable|file|max:10240')] // 10MB Max
    public $file = null;

    public ?string $selectedImageUrl = null;

    public float $weight = 0;

    public float $x_dimension = 0;

    public float $y_dimension = 0;

    public float $z_dimension = 0;

    public ?int $category_id = null;

    public ?int $brand_id = null;

    public ?int $unit_id = null;

    public string $category_search = '';

    public string $brand_search = '';

    public string $unit_search = '';

    public array $tags = [];

    public function updatedName(string $value): void
    {
        $this->slug = Str::slug($value);
        $this->slug_fa = slug_fa($value);
    }

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'en_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:products,slug'],
            'slug_fa' => ['required', 'string', 'max:255', 'unique:products,slug_fa'],
            'file' => ['nullable', 'file', 'max:10240'],
            'selectedImageUrl' => ['nullable', 'url'],
            'weight' => ['required', 'numeric', 'min:0'],
            'x_dimension' => ['required', 'numeric', 'min:0'],
            'y_dimension' => ['required', 'numeric', 'min:0'],
            'z_dimension' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
        ], [], [
            'file' => __('general.file'),
            'selectedImageUrl' => __('general.image'),
        ]);

        // Validate that either file or selectedImageUrl is provided
        if (! $this->file && ! $this->selectedImageUrl) {
            Flux::toast(variant: 'warning', text: __('general.file_or_image_required'));
            return;
        }

        $filePath = null;
        $fileName = null;
        $naming = [
            'slug_fa' => $validated['slug_fa'],
            'slug' => $validated['slug'],
            'name' => $validated['name'],
        ];
        $seo = app(ProductImageSeoService::class);

        if ($this->file) {
            $paths = $seo->storeAsWebp($this->file, 'products', $naming);
            $filePath = $paths['file_path'];
            $fileName = $paths['file_name'];
        } elseif ($this->selectedImageUrl) {
            try {
                $imageResponse = Http::timeout(60)->get($this->selectedImageUrl);

                if (! $imageResponse->successful()) {
                    Flux::toast(variant: 'danger', text: __('general.error_downloading_image'));

                    return;
                }

                $paths = $seo->storeAsWebp($imageResponse->body(), 'products', $naming);
                $filePath = $paths['file_path'];
                $fileName = $paths['file_name'];
            } catch (\Exception $e) {
                Flux::toast(variant: 'danger', text: __('general.error_downloading_image').': '.$e->getMessage());

                return;
            }
        }

        $product = Product::create([
            'name' => $validated['name'],
            'en_name' => $validated['en_name'],
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

        if (!empty($this->tags)) {
            $product->attachTags($this->tags);
        }

        Flux::modal('panel.shop.product.create.modal')->close();
        $this->dispatch('panel.shop.product.index.render');
        Flux::toast(variant: 'success', text: __('general.product_created'));
        $this->reset(['name', 'en_name', 'description', 'tags', 'slug', 'slug_fa', 'file', 'selectedImageUrl', 'weight', 'x_dimension', 'y_dimension', 'z_dimension', 'category_id', 'brand_id', 'unit_id', 'category_search', 'brand_search', 'unit_search']);
    }

    public function removeFile(): void
    {
        if ($this->file) {
            $this->file->delete();
            $this->file = null;
            Flux::toast(variant: 'success', text: __('general.file_removed'));
        }
    }

    public function removeSelectedImage(): void
    {
        $this->selectedImageUrl = null;
        Flux::toast(variant: 'success', text: __('general.image_removed'));
    }

    #[On('panel.shop.product.create.image-selected')]
    public function onImageSelected($url): void
    {
        // Remove existing file if any
        if ($this->file) {
            $this->file->delete();
            $this->file = null;
        }

        $this->selectedImageUrl = $url;
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
        // Only get subcategories (children), not root categories
        $query = Category::query()
            ->where('main_category_id', '!=', 0)
            ->when($this->category_search, fn ($query) => $query->where('name', 'like', '%'.$this->category_search.'%'))
            ->with('main_category')
            ->orderBy('name')
            ->limit(20);

        $categories = $query->get(['id', 'name', 'main_category_id']);

        // If a category is selected but not in the filtered results, include it
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

        // If a brand is selected but not in the filtered results, include it
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

        // If a unit is selected but not in the filtered results, include it
        if ($this->unit_id && !$units->contains('id', $this->unit_id)) {
            $selectedUnit = Unit::where('id', $this->unit_id)->first(['id', 'name']);

            if ($selectedUnit) {
                $units->prepend($selectedUnit);
            }
        }

        return $units;
    }

    public function render(): View
    {
        return view('livewire.panel.shop.product.create');
    }
}
