<?php

namespace App\Livewire\Main\Product;

use App\Models\Shop\Product;
use App\Models\Shop\ProductPrice;
use Binafy\LaravelCart\Models\Cart;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;

class View extends Component
{
    public $id = null;

    public $slug = null;

    public $selectedColorId = null;

    public $selectedWarrantyId = null;

    public $quantity = 1;

    public $selectedImageIndex = 0;

    public function mount($id = null, $slug = null)
    {
        $this->id = $id;
        $this->slug = $slug;

        if (! $this->product) {
            abort(404);
        }

        $canonicalSlug = $this->product->slug_fa ?: $this->product->slug;
        if ($this->slug === null || $this->slug !== $canonicalSlug) {
            $this->redirect($this->product->url, navigate: false);
            // Force a 301 for SEO when slug is missing/wrong
            abort(redirect()->to($this->product->url, 301));
        }
    }

    #[Computed]
    public function product()
    {
        if (! $this->id) {
            return null;
        }

        return Product::query()
            ->with([
                'category.attributes.attributeGroup',
                'category.attributes.options',
                'brand',
                'unit',
                'colors',
                'warranties',
                'prices.color',
                'prices.warranty',
                'attributeValues.attribute.attributeGroup',
                'attributeValues.attribute.options',
                'images',
            ])
            ->where('id', $this->id)
            ->first();
    }

    #[Computed]
    public function selectedPrice()
    {
        if (! $this->product) {
            return null;
        }

        $query = ProductPrice::query()
            ->where('product_id', $this->product->id)
            ->where('quantity', '>', 0);

        if ($this->selectedColorId) {
            $query->where('color_id', $this->selectedColorId);
        } else {
            $query->whereNull('color_id');
        }

        if ($this->selectedWarrantyId) {
            $query->where('warranty_id', $this->selectedWarrantyId);
        } else {
            $query->whereNull('warranty_id');
        }

        $price = $query->first();

        // If no exact match, try to find a price with just color or just warranty
        if (! $price && $this->selectedColorId) {
            $price = ProductPrice::query()
                ->where('product_id', $this->product->id)
                ->where('color_id', $this->selectedColorId)
                ->whereNull('warranty_id')
                ->where('quantity', '>', 0)
                ->first();
        }

        if (! $price && $this->selectedWarrantyId) {
            $price = ProductPrice::query()
                ->where('product_id', $this->product->id)
                ->where('warranty_id', $this->selectedWarrantyId)
                ->whereNull('color_id')
                ->where('quantity', '>', 0)
                ->first();
        }

        // If still no match, use default price
        if (! $price) {
            $default = $this->product->default_price;
            $price = $default['record'] ?? null;
        }

        return $price;
    }

    public function selectColor($colorId)
    {
        $this->selectedColorId = $colorId;
        $this->dispatch('price-updated');
    }

    public function selectWarranty($warrantyId)
    {
        $this->selectedWarrantyId = $warrantyId;
        $this->dispatch('price-updated');
    }

    public function increaseQuantity()
    {
        if ($this->selectedPrice && $this->quantity < $this->selectedPrice->quantity) {
            $this->quantity++;
        }
    }

    public function decreaseQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function selectImage($index)
    {
        $this->selectedImageIndex = $index;
    }

    #[Computed]
    public function allImages()
    {
        if (! $this->product) {
            return collect();
        }

        $images = collect();

        // Add main product image first
        if ($this->product->file_path) {
            $images->push([
                'type' => 'main',
                'file_path' => $this->product->file_path,
                'file_name' => $this->product->file_name,
            ]);
        }

        // Add additional images
        foreach ($this->product->images as $image) {
            $images->push([
                'type' => 'additional',
                'file_path' => $image->file_path,
                'file_name' => $image->file_name,
            ]);
        }

        return $images;
    }

    #[Computed]
    public function currentImage()
    {
        $images = $this->allImages();
        
        if ($images->isEmpty()) {
            return null;
        }

        $index = min($this->selectedImageIndex, $images->count() - 1);
        
        return $images->get($index);
    }

    public function delete(): void
    {
        if (! $this->product) {
            Flux::toast(variant: 'danger', text: __('app.product_not_found'));

            return;
        }

        $this->product->delete();
        Flux::toast(variant: 'success', text: __('app.product_deleted'));

        $this->redirect(route('panel.shop.product.index'), navigate: true);
    }

    public function addToCart()
    {
        $this->dispatch('main.sidebar.basket.refresh-cart');
        // Open basket modal after adding item
        Flux::modal('main.sidebar.basket.modal')->show();

        if (! auth()->check()) {
            Flux::toast(variant: 'danger', text: __('app.please_login_to_add_to_cart'));

            return $this->redirect(route('login'), navigate: true);
        }

        if (! $this->product) {
            Flux::toast(variant: 'danger', text: __('app.product_not_found'));

            return;
        }

        $selectedPrice = $this->selectedPrice();

        if (! $selectedPrice || $selectedPrice->quantity < $this->quantity) {
            Flux::toast(variant: 'danger', text: __('app.insufficient_quantity'));

            return;
        }

        try {
            $cart = Cart::query()->firstOrCreate(['user_id' => auth()->id()]);

            // Prepare options for cart item
            $options = [];
            if ($this->selectedColorId) {
                $color = $this->product->colors->firstWhere('id', $this->selectedColorId);
                if ($color) {
                    $options['color'] = [
                        'id' => $color->id,
                        'name' => $color->name,
                        'hex' => $color->hex,
                    ];
                }
            }

            if ($this->selectedWarrantyId) {
                $warranty = $this->product->warranties->firstWhere('id', $this->selectedWarrantyId);
                if ($warranty) {
                    $options['warranty'] = [
                        'id' => $warranty->id,
                        'name' => $warranty->name,
                    ];
                }
            }

            $options['price_id'] = $selectedPrice->id;

            // Check if item already exists in cart with same options
            $existingItem = $cart->items()
                ->where('itemable_id', $this->product->id)
                ->where('itemable_type', Product::class)
                ->whereJsonContains('options->price_id', $selectedPrice->id)
                ->first();

            if ($existingItem) {
                // Check stock before increasing
                $newQuantity = $existingItem->quantity + $this->quantity;
                if ($newQuantity <= $selectedPrice->quantity) {
                    $existingItem->increment('quantity', $this->quantity);
                } else {
                    Flux::toast(variant: 'danger', text: __('app.insufficient_quantity'));

                    return;
                }
            } else {
                // Add new item
                $cart->storeItem([
                    'itemable' => $this->product,
                    'quantity' => $this->quantity,
                    'options' => json_encode($options),
                ]);
            }

            Flux::toast(variant: 'success', text: __('app.product_added_to_cart'));
            $this->dispatch('main.sidebar.basket.refresh-cart');
            // Open basket modal after adding item
            Flux::modal('main.sidebar.basket.modal')->show();
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
            //Flux::toast(variant: 'danger', text: __('app.failed_to_add_to_cart'));
        }
    }

    public function render()
    {
        if (! $this->product) {
            abort(404);
        }

        $canonicalSlug = $this->product->slug_fa ?: $this->product->slug;
        if ($this->slug === null || $this->slug !== $canonicalSlug) {
            return redirect()->to($this->product->url, 301);
        }

        return view('livewire.main.product.view');
    }
}
