<?php

namespace App\Livewire\Panel\Shop\Sepidar\Invoice;

use App\Models\Sepidar\INV\InventoryReceiptItem;
use App\Models\Sepidar\SLS\Invoice;
use Livewire\Attributes\On;
use Livewire\Component;

class View extends Component
{
    public Invoice $invoice;
    #[On('panel.shop.sepidar.invoice.view.assign-data')]
    public function assignData($id): void
    {
        $this->invoice = Invoice::with(['items', 'items.item', 'items.last_receipt'])->findOrFail($id);
    }
    public function render()
    {
        return view('livewire.panel.shop.sepidar.invoice.view');
    }
}
