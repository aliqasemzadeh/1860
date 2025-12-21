<?php

namespace App\Livewire\Main\Dashboard;

use App\Models\Shop\Category;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    #[Computed(cache: true, key: 'categories_root')]
    public function categories()
    {
        return Category::query()
            ->where('main_category_id', 0)
            ->get();
    }

    public function render()
    {
        return view('livewire.main.dashboard.index');
    }
}
