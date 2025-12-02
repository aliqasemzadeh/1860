<?php

namespace App\Livewire\Main\Sidebar;

use App\Models\Shop\Category;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Categories extends Component
{
    #[Computed(cache: true)]
    public function categories()
    {
        return Category::query()
            ->get();
    }
    public function render()
    {
        return view('livewire.main.sidebar.categories');
    }
}
