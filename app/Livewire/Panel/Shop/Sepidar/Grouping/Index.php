<?php

namespace App\Livewire\Panel\Shop\Sepidar\Grouping;

use App\Models\Sepidar\GNR\Grouping;
use Livewire\Component;

class Index extends Component
{
    public Grouping $grouping;
    public function mount(int $groupingId = 1): void
    {
        $this->grouping = Grouping::where('id', $groupingId)->firstOrFail();
    }
    public function render()
    {
        $mainGrouping = Grouping::where('ParentGroupRef', null)->get();
        return view('livewire.panel.shop.sepidar.grouping.index', ['mainGrouping' => $mainGrouping]);
    }
}
