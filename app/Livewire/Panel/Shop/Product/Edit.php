<?php

namespace App\Livewire\Panel\Shop\Product;

use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\Product as ProductModel;
use App\Models\Shop\Unit;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public ProductModel $product;

    public int $id;

    public string $name = '';

    public ?string $description = null;

    public string $slug = '';

    public string $slug_fa = '';

    public string $category_search = '';

    public string $brand_search = '';

    public string $unit_search = '';

    #[Validate('nullable|file|max:10240')] // 10MB Max
    public $file = null;

    public float $weight = 0;

    public float $x_dimension = 0;

    public float $y_dimension = 0;

    public float $z_dimension = 0;

    public ?int $category_id = null;

    public ?int $brand_id = null;

    public ?int $unit_id = null;

    public function updatedName(string $value): void
    {
        $this->slug = Str::slug($value);
        $this->slug_fa = slug_fa( $value);
    }

    #[On('panel.shop.product.edit.assign-data')]
    public function assignData($id): void
    {
        $this->product = ProductModel::findOrFail($id);
        $this->id = $this->product->id;
        $this->name = (string) $this->product->name;
        $this->description = $this->product->description;
        $this->slug = (string) $this->product->slug;
        $this->slug_fa = (string) $this->product->slug_fa;
        $this->weight = (float) $this->product->weight;
        $this->x_dimension = (float) $this->product->x_dimension;
        $this->y_dimension = (float) $this->product->y_dimension;
        $this->z_dimension = (float) $this->product->z_dimension;
        $this->category_id = $this->product->category_id;
        $this->brand_id = $this->product->brand_id;
        $this->unit_id = $this->product->unit_id;
        $this->file = null;
        $this->category_search = '';
        $this->brand_search = '';
        $this->unit_search = '';
        Flux::modal('panel.shop.product.edit.modal')->show();
    }

    public function edit(): void
    {
        if (! isset($this->product)) {
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('products', 'slug')->ignore($this->product)],
            'slug_fa' => ['required', 'string', 'max:255', Rule::unique('products', 'slug_fa')->ignore($this->product)],
            'file' => ['nullable', 'file', 'max:10240'],
            'weight' => ['required', 'numeric', 'min:0'],
            'x_dimension' => ['required', 'numeric', 'min:0'],
            'y_dimension' => ['required', 'numeric', 'min:0'],
            'z_dimension' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'slug' => $validated['slug'],
            'slug_fa' => $validated['slug_fa'],
            'weight' => $validated['weight'],
            'x_dimension' => $validated['x_dimension'],
            'y_dimension' => $validated['y_dimension'],
            'z_dimension' => $validated['z_dimension'],
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'unit_id' => $validated['unit_id'],
        ];

        // If a new file is uploaded, replace the old one
        if ($this->file) {
            // Delete old file
            if ($this->product->file_path && Storage::disk('public')->exists($this->product->file_path)) {
                Storage::disk('public')->delete($this->product->file_path);
            }

            // Store new file publicly with original name
            $filePath = $this->file->storeAs('products', $this->file->getClientOriginalName(), 'public');
            $fileName = $this->file->getClientOriginalName();

            $updateData['file_path'] = $filePath;
            $updateData['file_name'] = $fileName;
        }

        $this->product->fill($updateData)->save();

        $this->dispatch('shop.product.index.render');
        Flux::modal('panel.shop.product.edit.modal')->close();
        Flux::toast(variant: 'success', text: __('app.product_updated'));
    }

    public function removeFile(): void
    {
        if ($this->file) {
            $this->file->delete();
            $this->file = null;
            Flux::toast(variant: 'success', text: __('app.file_removed'));
        }
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
        return view('livewire.panel.shop.product.edit');
    }
}
