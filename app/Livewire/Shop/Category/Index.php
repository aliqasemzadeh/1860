<?php

namespace App\Livewire\Shop\Category;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('layouts.panels.shop')]
    public function render()
    {
        return view('livewire.shop.category.index');
    }
}
