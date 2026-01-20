<?php

namespace App\Livewire\Panel\Administrator\Dashboard;

use App\Models\Sepidar\INV\InventoryReceipt;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Computed]
    public function inventory()
    {
        $itemReceipts = InventoryReceipt::query()
            ->where('Fis')
    }

    #[Layout('layouts.panels.administrator')]
    public function render()
    {
        $this->authorize('administrator_dashboard_index');
        return view('livewire.panel.administrator.dashboard.index');
    }
}
