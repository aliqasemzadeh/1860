<?php

namespace App\Livewire\Main\Sidebar;

use App\Models\Shop\Product;
use Binafy\LaravelCart\Models\Cart;
use Binafy\LaravelCart\Models\CartItem;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Basket extends Component
{
    #[Computed]
    public function cart()
    {
        if (!auth()->check()) {
            return null;
        }

        return Cart::query()
            ->with(['items.itemable'])
            ->where('user_id', auth()->id())
            ->first();
    }

    #[Computed]
    public function cartItems()
    {
        if (!$this->cart) {
            return collect();
        }

        return $this->cart->items;
    }

    #[Computed]
    public function totalAmount()
    {
        if (!$this->cart) {
            return 0;
        }

        $total = 0;
        foreach ($this->cart->items as $item) {
            $price = $item->itemable->getPrice();
            // Get price from options if available
            $options = is_string($item->options) ? json_decode($item->options, true) : $item->options;
            $priceId = $options['price_id'] ?? null;
            if ($priceId) {
                $priceRecord = \App\Models\Shop\ProductPrice::find($priceId);
                if ($priceRecord) {
                    $price = $priceRecord->sale_price && $priceRecord->sale_price < $priceRecord->price 
                        ? $priceRecord->sale_price 
                        : $priceRecord->price;
                }
            }
            $total += $price * $item->quantity;
        }

        return $total;
    }

    public function increaseQuantity($itemId)
    {
        if (!auth()->check()) {
            return;
        }

        $item = CartItem::find($itemId);
        if ($item && $item->itemable instanceof Product) {
            // Check if we can increase quantity (check stock)
            $selectedPrice = $this->getPriceForItem($item);
            if ($selectedPrice && $item->quantity < $selectedPrice->quantity) {
                $item->increment('quantity');
                session()->flash('success', __('app.quantity_increased'));
            } else {
                session()->flash('error', __('app.max_quantity_reached'));
            }
            $this->dispatch('cart-updated');
        }
    }

    public function decreaseQuantity($itemId)
    {
        if (!auth()->check()) {
            return;
        }

        $item = CartItem::find($itemId);
        if ($item && $item->quantity > 1) {
            $item->decrement('quantity');
            session()->flash('success', __('app.quantity_decreased'));
            $this->dispatch('cart-updated');
        } elseif ($item && $item->quantity == 1) {
            // Remove item if quantity is 1
            $this->removeItem($itemId);
        }
    }

    public function removeItem($itemId)
    {
        if (!auth()->check()) {
            return;
        }

        $item = CartItem::find($itemId);
        if ($item) {
            // Delete the specific cart item directly to preserve options (color/warranty)
            $item->delete();
            session()->flash('success', __('app.item_removed_from_cart'));
            $this->dispatch('cart-updated');
        }
    }

    protected function getPriceForItem($cartItem)
    {
        if (!$cartItem->itemable instanceof Product) {
            return null;
        }

        $options = is_string($cartItem->options) ? json_decode($cartItem->options, true) : $cartItem->options;
        $priceId = $options['price_id'] ?? null;

        if ($priceId) {
            return \App\Models\Shop\ProductPrice::find($priceId);
        }

        return $cartItem->itemable->default_price['record'] ?? null;
    }

    #[On('cart-updated')]
    public function refreshCart()
    {
        $this->reset('cart', 'cartItems', 'totalAmount');
    }

    #[On('open-basket-modal')]
    public function openBasketModal()
    {
        // Refresh cart data when modal is opened
        $this->refreshCart();
    }

    public function render()
    {
        return view('livewire.main.sidebar.basket');
    }
}
