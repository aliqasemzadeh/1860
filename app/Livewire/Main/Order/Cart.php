<?php

namespace App\Livewire\Main\Order;

use App\Models\Shop\Product;
use App\Models\Shop\ProductPrice;
use Binafy\LaravelCart\Models\Cart as UserCart;
use Binafy\LaravelCart\Models\CartItem;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app')]
class Cart extends Component
{
    #[Computed]
    public function cart()
    {
        if (! auth()->check()) {
            return null;
        }

        return UserCart::query()
            ->with(['items.itemable'])
            ->where('user_id', auth()->id())
            ->first();
    }

    #[Computed]
    public function cartItems()
    {
        if (! $this->cart) {
            return collect();
        }

        return $this->cart->items;
    }

    #[Computed]
    public function hasUnavailableItems(): bool
    {
        return $this->cartItems->contains(fn (CartItem $item): bool => ! $this->isItemPurchasable($item));
    }

    /**
     * All selected price rows for the current cart, loaded in a single query.
     *
     * @return array<int, ProductPrice>
     */
    #[Computed]
    public function priceMap(): array
    {
        $priceIds = $this->cartItems
            ->map(function (CartItem $item) {
                $options = is_string($item->options) ? json_decode($item->options, true) : $item->options;

                return $options['price_id'] ?? null;
            })
            ->filter()
            ->unique()
            ->values();

        if ($priceIds->isEmpty()) {
            return [];
        }

        return ProductPrice::query()
            ->whereIn('id', $priceIds)
            ->get()
            ->keyBy('id')
            ->all();
    }

    /**
     * Effective unit price for a cart item (variant-aware).
     */
    public function itemPrice(CartItem $item): float
    {
        $options = is_string($item->options) ? json_decode($item->options, true) : $item->options;
        $priceId = $options['price_id'] ?? null;
        $priceRecord = $priceId ? ($this->priceMap[$priceId] ?? null) : null;

        if ($priceRecord) {
            return (float) ($priceRecord->sale_price && $priceRecord->sale_price < $priceRecord->price
                ? $priceRecord->sale_price
                : $priceRecord->price);
        }

        return (float) ($item->itemable?->getPrice() ?? 0);
    }

    #[Computed]
    public function totalAmount()
    {
        if (! $this->cart) {
            return 0;
        }

        $total = 0;
        foreach ($this->cart->items as $item) {
            if (! $this->isItemPurchasable($item)) {
                continue;
            }

            $total += $this->itemPrice($item) * $item->quantity;
        }

        return $total;
    }

    public function increaseQuantity($itemId)
    {
        if (! auth()->check()) {
            return;
        }

        $item = CartItem::find($itemId);
        if ($item && $item->itemable instanceof Product) {
            // Check if we can increase quantity (check stock)
            $selectedPrice = $this->getPriceForItem($item);
            if ($item->itemable->isPurchasable($selectedPrice, (float) $item->quantity + 1)) {
                $item->increment('quantity');
                Flux::toast(variant: 'success', text: __('general.quantity_increased'));
            } else {
                Flux::toast(variant: 'danger', text: __('general.max_quantity_reached'));
            }
            $this->dispatch('main.sidebar.basket.refresh-cart');
        }
    }

    public function decreaseQuantity($itemId)
    {
        if (! auth()->check()) {
            return;
        }

        $item = CartItem::find($itemId);
        if ($item && $item->quantity > 1) {
            $item->decrement('quantity');
            Flux::toast(variant: 'success', text: __('general.quantity_decreased'));
            $this->dispatch('main.sidebar.basket.refresh-cart');
        } elseif ($item && $item->quantity == 1) {
            // Remove item if quantity is 1
            $this->removeItem($itemId);
        }
    }

    public function removeItem($itemId)
    {
        if (! auth()->check()) {
            return;
        }

        $item = CartItem::find($itemId);
        if ($item) {
            $item->delete();
            Flux::toast(variant: 'success', text: __('general.item_removed_from_cart'));
            $this->dispatch('main.sidebar.basket.refresh-cart');
        }
    }

    protected function getPriceForItem($cartItem)
    {
        if (! $cartItem->itemable instanceof Product) {
            return null;
        }

        $options = is_string($cartItem->options) ? json_decode($cartItem->options, true) : $cartItem->options;
        $priceId = $options['price_id'] ?? null;

        if ($priceId) {
            return $this->priceMap[$priceId] ?? ProductPrice::find($priceId);
        }

        return $cartItem->itemable->default_price['record'] ?? null;
    }

    protected function isItemPurchasable(CartItem $cartItem): bool
    {
        if (! $cartItem->itemable instanceof Product) {
            return false;
        }

        return $cartItem->itemable->isPurchasable(
            $this->getPriceForItem($cartItem),
            (float) $cartItem->quantity,
        );
    }

    #[On('main.sidebar.basket.refresh-cart')]
    public function refreshCart()
    {
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.main.order.cart');
    }
}
