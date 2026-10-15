<?php

namespace App\Livewire\Main\Content\Box;

use App\Models\Content\Box;
use App\Models\Shop\Product;
use Binafy\LaravelCart\Models\Cart;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public const PRODUCTS_PER_BOX = 4;

    #[Computed]
    public function boxes()
    {
        return Box::query()
            ->where('is_active', true)
            ->ordered()
            ->with([
                'media',
                'products' => function ($query) {
                    $query->select('products.*')
                        ->active()
                        ->with('category:id,name')
                        ->withCount(['colors', 'warranties'])
                        ->withEffectivePrice()
                        ->orderByAvailability()
                        ->orderByDesc('products.created_at')
                        ->limit(self::PRODUCTS_PER_BOX);
                },
            ])
            ->get();
    }

    /**
     * Add the product's default variant to the cart directly from the brand box.
     * Products that require a color/warranty choice are redirected to their page.
     */
    public function quickAdd(int $productId)
    {
        if (! auth()->check()) {
            Flux::toast(variant: 'danger', text: __('general.please_login_to_add_to_cart'));

            return $this->redirect(route('login'), navigate: true);
        }

        $product = Product::query()
            ->active()
            ->withCount(['colors', 'warranties'])
            ->find($productId);

        if (! $product) {
            Flux::toast(variant: 'danger', text: __('general.product_not_found'));

            return;
        }

        if ($product->colors_count > 0 || $product->warranties_count > 0) {
            return $this->redirect($product->url, navigate: true);
        }

        $price = $product->default_price['record'] ?? null;

        if (! $product->isPurchasable($price)) {
            Flux::toast(variant: 'danger', text: __('general.product_unavailable'));

            return;
        }

        try {
            $cart = Cart::query()->firstOrCreate(['user_id' => auth()->id()]);

            $existingItem = $cart->items()
                ->where('itemable_id', $product->id)
                ->where('itemable_type', Product::class)
                ->whereJsonContains('options->price_id', $price->id)
                ->first();

            if ($existingItem) {
                if ($existingItem->quantity + 1 > $price->quantity) {
                    Flux::toast(variant: 'danger', text: __('general.insufficient_quantity'));

                    return;
                }

                $existingItem->increment('quantity');
            } else {
                $cart->storeItem([
                    'itemable' => $product,
                    'quantity' => 1,
                    'options' => json_encode(['price_id' => $price->id]),
                ]);
            }

            Flux::toast(variant: 'success', text: __('general.product_added_to_cart'));
            $this->dispatch('main.sidebar.basket.refresh-cart');
            Flux::modal('main.sidebar.basket.modal')->show();
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.main.content.box.index');
    }
}
