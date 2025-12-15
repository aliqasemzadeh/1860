<?php

namespace App\Livewire\Shop\Product\Pricing;

use App\Models\Shop\Color;
use App\Models\Shop\Product;
use App\Models\Shop\ProductPrice;
use App\Models\Shop\Warranty;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class Create extends Component
{
    #[Locked]
    public $productId;
    public ?Product $product = null;

    public ?int $color_id = null;
    public ?int $warranty_id = null;
    public string $price = '0';
    public string $sale_price = '0';
    public ?string $quantity = '0';
    public bool $is_default = false;

    public string $color_search = '';
    public string $warranty_search = '';

    public function mount($productId = null)
    {
        if ($productId) {
            $this->productId = $productId;
            $this->loadProduct();
        } elseif ($this->productId) {
            // ProductId might be set via property binding
            $this->loadProduct();
        }
    }

    public function boot()
    {
        // Ensure product is loaded if productId is available
        if ($this->productId && !$this->product) {
            $this->loadProduct();
        }
    }

    public function updatedProductId()
    {
        if ($this->productId) {
            $this->loadProduct();
        }
    }

    protected function loadProduct(): void
    {
        if ($this->productId) {
            $this->product = Product::with(['colors', 'warranties'])->findOrFail($this->productId);
        }
    }

    #[On('shop.product.pricing.create.modal.opened')]
    public function resetForm(): void
    {
        $this->color_id = null;
        $this->warranty_id = null;
        $this->price = '';
        $this->sale_price = '';
        $this->quantity = '0';
        $this->is_default = false;
        $this->color_search = '';
        $this->warranty_search = '';
        $this->resetValidation();
    }

    public function create()
    {
        // Ensure product is loaded
        if (!$this->product && $this->productId) {
            $this->loadProduct();
        }

        if (!$this->product || !$this->productId) {
            Flux::toast(variant: 'danger', text: __('app.product_not_found'));
            return;
        }

        // Convert empty strings to null for optional fields
        $this->color_id = $this->color_id === '' ? null : $this->color_id;
        $this->warranty_id = $this->warranty_id === '' ? null : $this->warranty_id;

        $validated = $this->validate([
            'price' => ['required'],
            'sale_price' => ['nullable'],
            'quantity' => ['required', 'numeric'],
            'color_id' => ['nullable', 'exists:colors,id'],
            'warranty_id' => ['nullable', 'exists:warranties,id'],
            'is_default' => ['boolean'],
        ], [
            'price.required' => __('app.price_required'),
            'price.numeric' => __('app.price_must_be_numeric'),
            'sale_price.numeric' => __('app.sale_price_must_be_numeric'),
            'quantity.required' => __('app.quantity_required'),
            'quantity.numeric' => __('app.quantity_must_be_numeric'),
            'color_id.exists' => __('app.color_not_found'),
            'warranty_id.exists' => __('app.warranty_not_found'),
        ]);

        // If setting as default, unset other defaults for this product with same color/warranty combination
        if ($validated['is_default']) {
            ProductPrice::where('product_id', $this->productId)
                ->where(function ($query) use ($validated) {
                    $query->where('color_id', $validated['color_id'] ?? null)
                        ->where('warranty_id', $validated['warranty_id'] ?? null);
                })
                ->update(['is_default' => false]);
        }

        ProductPrice::create([
            'product_id' => $this->productId,
            'color_id' => $validated['color_id'],
            'warranty_id' => $validated['warranty_id'],
            'price' => str_replace(",","", $validated['price']),
            'sale_price' => str_replace(",","", $validated['sale_price']),
            'quantity' => $validated['quantity'],
            'is_default' => $validated['is_default'],
        ]);


        Flux::toast(variant: 'success', text: __('app.price_created'));
        Flux::modal('shop.product.pricing.create.modal')->close();
        return redirect(route('shop.product.pricing.index', [$this->productId]));
    }

    #[Computed]
    public function colors()
    {
        if (!$this->product) {
            return collect();
        }

        return $this->product->colors()
            ->when($this->color_search, fn($query) => $query->where('colors.name', 'like', '%' . $this->color_search . '%'))
            ->select('colors.id', 'colors.name', 'colors.hex')
            ->orderBy('colors.name')
            ->get();
    }

    #[Computed]
    public function warranties()
    {
        if (!$this->product) {
            return collect();
        }

        return $this->product->warranties()
            ->when($this->warranty_search, fn($query) => $query->where('warranties.name', 'like', '%' . $this->warranty_search . '%'))
            ->select('warranties.id', 'warranties.name', 'warranties.slug', 'warranties.slug_fa')
            ->orderBy('warranties.name')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.shop.product.pricing.create');
    }
}
