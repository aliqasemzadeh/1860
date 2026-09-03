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
            ->active()
            ->with(['category'])
            ->whereAvailability(true)
            ->orderByDesc('created_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.main.dashboard.index');
    }
}
