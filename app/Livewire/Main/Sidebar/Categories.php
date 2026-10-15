<?php

namespace App\Livewire\Main\Sidebar;

use App\Models\Shop\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Categories extends Component
{
    #[Computed]
    public function categories(): Collection
    {
        return Cache::rememberForever(Category::CACHE_KEY_TREE, function () {
            return Category::query()
                ->with(['children' => function ($query) {
                    $query->orderBy('sort_order', 'asc')
                        ->orderBy('name', 'asc')
                        ->with(['children' => function ($subQuery) {
                            $subQuery->orderBy('sort_order', 'asc')
                                ->orderBy('name', 'asc');
                        }]);
                }])
                ->where('main_category_id', 0)
                ->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        });
    }

    public function render()
    {
        return view('livewire.main.sidebar.categories');
    }
}
