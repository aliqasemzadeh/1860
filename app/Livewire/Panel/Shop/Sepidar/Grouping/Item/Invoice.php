<?php

namespace App\Livewire\Panel\Shop\Sepidar\Grouping\Item;

use App\Models\Sepidar\SLS\InvoiceItem;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Invoice extends Component
{
    public $ItemId;


    #[On('panel.shop.sepidar.grouping.item.invoice.assign-data')]
    public function assignData($id): void
    {
        $this->ItemId = $id;
        Flux::modal('panel.shop.sepidar.grouping.item.invoice.modal')->show();
    }

    #[Computed]
    public function sales()
    {
        return InvoiceItem::with(['invoice'])
            ->where('ItemRef', $this->ItemId)
            ->latest('InvoiceItemId')
            ->get();
    }

    public function render()
    {
        return view('livewire.panel.shop.sepidar.grouping.item.invoice');
    }
}
