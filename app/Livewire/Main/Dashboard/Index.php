<?php

namespace App\Livewire\Main\Dashboard;

use App\Models\Shop\Category;
use App\Models\Shop\Product;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    #[Computed]
    public function categories()
    {
        return Category::query()
            ->where('main_category_id', 0)
            ->get();
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->with(['category'])
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn ($product) => ($product->default_price['available'] ?? false) === true)
            ->values();
    }

    public function render()
    {
        return view('livewire.main.dashboard.index');
    }
}
