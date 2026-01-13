<?php

namespace App\Livewire\Panel\Shop\Sepidar\Grouping;

use App\Models\Sepidar\GNR\Grouping;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $mainGrouping = Grouping::where('ParentGroupRef', null)->get();
        return view('livewire.panel.shop.sepidar.grouping.index', ['mainGrouping' => $mainGrouping]);
    }
}
