<?php

namespace App\Livewire\Panel\Shop\Sepidar\Party;

use App\Models\Sepidar\GNR\Party;
use App\Models\Sepidar\SLS\Invoice;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Invoices extends Component
{
    public ?Party $party = null;

    #[On('panel.shop.sepidar.party.invoices.assign-data')]
    public function assignData($id): void
    {
        $this->party = Party::findOrFail($id);
        Flux::modal('panel.shop.sepidar.party.invoices.modal')->show();
    }

    #[Computed]
    public function invoices()
    {
        if (!$this->party) {
            return collect();
        }

        return Invoice::query()
            ->where('CustomerPartyRef', $this->party->PartyId)
            ->get();
    }

    public function render()
    {
        return view('livewire.panel.shop.sepidar.party.invoices');
    }
}
