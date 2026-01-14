<?php

namespace App\Livewire\Panel\Shop\Sepidar\Grouping;

use App\Models\Sepidar\GNR\Grouping;
use App\Models\Sepidar\INV\Item;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Items extends Component
{
    public string $search = '';
    public Grouping $grouping;

    public function mount(int $groupingId): void
    {
        $this->grouping = Grouping::where('GroupingID', $groupingId)->first();
    }

    #[Computed]
    public function items()
    {
        return Item::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('Title', 'like', '%' . $this->search . '%');
                });
            })
            ->where('CodingGroupRef', $this->grouping->GroupingID)
            ->get();
    }

    public function render()
    {
        return view('livewire.panel.shop.sepidar.grouping.items');
    }
}
