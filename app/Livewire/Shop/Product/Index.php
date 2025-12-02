<?php

namespace App\Livewire\Shop\Product;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('layouts.panels.shop')]
    public function render()
    {
        return view('livewire.shop.product.index');
    }
}
