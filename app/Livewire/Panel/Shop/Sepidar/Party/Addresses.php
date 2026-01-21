<?php

namespace App\Livewire\Panel\Shop\Sepidar\Party;

use Livewire\Attributes\Computed;
use Livewire\Component;

class Addresses extends Component
{
    #[Computed]
    public function addresses()
    {

    }


    public function render()
    {
        return view('livewire.panel.shop.sepidar.party.addresses');
    }
}
