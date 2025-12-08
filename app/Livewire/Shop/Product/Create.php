<?php

namespace App\Livewire\Shop\Product;

use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\Unit;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $slug = '';

    public string $slug_fa = '';

    #[Validate('nullable|file|max:10240')] // 10MB Max
    public $file = null;

    public float $weight = 0;

    public float $x_dimension = 0;

    public float $y_dimension = 0;

    public float $z_dimension = 0;

    public ?int $category_id = null;

    public ?int $brand_id = null;

    public ?int $unit_id = null;

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:products,slug'],
            'slug_fa' => ['required', 'string', 'max:255', 'unique:products,slug_fa'],
            'file' => ['required', 'file', 'max:10240'],
            'weight' => ['required', 'numeric', 'min:0'],
            'x_dimension' => ['required', 'numeric', 'min:0'],
            'y_dimension' => ['required', 'numeric', 'min:0'],
            'z_dimension' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
        ]);

        // Store file publicly with original name
        $filePath = $this->file->storeAs('products', $this->file->getClientOriginalName(), 'public');
        $fileName = $this->file->getClientOriginalName();

        Product::create([
            'name' => $validated['name'],
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

        Flux::modal('shop.product.create.modal')->close();
        $this->dispatch('shop.product.index.render');
        Flux::toast(variant: 'success', text: __('app.product_created'));
        $this->reset(['name', 'slug', 'slug_fa', 'file', 'weight', 'x_dimension', 'y_dimension', 'z_dimension', 'category_id', 'brand_id', 'unit_id']);
    }

    public function removeFile(): void
    {
        if ($this->file) {
            $this->file->delete();
            $this->file = null;
            Flux::toast(variant: 'success', text: __('app.file_removed'));
        }
    }

    public function render(): View
    {
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);
        $brands = Brand::query()->orderBy('name')->get(['id', 'name']);
        $units = Unit::query()->orderBy('name')->get(['id', 'name']);

        return view('livewire.shop.product.create', compact('categories', 'brands', 'units'));
    }
}
