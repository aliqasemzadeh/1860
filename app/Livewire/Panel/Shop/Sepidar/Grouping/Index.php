<?php

namespace App\Livewire\Panel\Shop\Sepidar\Grouping;

use App\Models\Sepidar\GNR\Grouping;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public Grouping $grouping;
    public function mount(int $groupingId = 1): void
    {
        $this->grouping = Grouping::where('id', $groupingId)->firstOrFail();
    }

    #[Computed(cache: true)]
    public function groupings()
    {
        return Grouping::query()
            ->where('ParentGroupRef', null)
            ->get();
    }
    public function render()
    {
        return view('livewire.panel.shop.sepidar.grouping.index');
    }
}
