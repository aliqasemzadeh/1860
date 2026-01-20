<?php

namespace App\Livewire\Panel\Shop\Sepidar\Invoice;

use App\Models\Sepidar\INV\InventoryReceiptItem;
use App\Models\Sepidar\SLS\Invoice;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

class View extends Component
{
    public Invoice $invoice;
    #[On('panel.shop.sepidar.invoice.view.assign-data')]
    public function assignData($id): void
    {
        $this->invoice = Invoice::with(['items', 'items.item'])->findOrFail($id);
        Flux::modal('panel.shop.sepidar.invoice.view.modal')->show();
    }
    public function render()
    {
        return view('livewire.panel.shop.sepidar.invoice.view');
    }
}
