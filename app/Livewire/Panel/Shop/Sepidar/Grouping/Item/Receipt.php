<?php

namespace App\Livewire\Panel\Shop\Sepidar\Grouping\Item;

use App\Models\Sepidar\INV\InventoryReceiptItem;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Receipt extends Component
{
    public $ItemId;

    #[On('panel.shop.sepidar.grouping.item.receipt.assign-data')]
    public function assignData($id): void
    {
        $this->ItemId = $id;
        Flux::modal('panel.shop.sepidar.grouping.item.receipt.modal')->show();
    }

    #[Computed]
    public function buys()
    {
        return InventoryReceiptItem::with(['receipt'])
            ->where('ItemRef', $this->ItemId)
            ->latest('InventoryReceiptItemId')
            ->get();
    }
    public function render()
    {
        return view('livewire.panel.shop.sepidar.grouping.item.receipt');
    }
}
