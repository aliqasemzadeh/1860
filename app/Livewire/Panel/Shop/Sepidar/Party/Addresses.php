<?php

namespace App\Livewire\Panel\Shop\Sepidar\Party;

use App\Models\Sepidar\GNR\Party;
use App\Models\Sepidar\GNR\PartyAddress;
use App\Models\Sepidar\GNR\PartyPhone;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Addresses extends Component
{
    public ?Party $party = null;

    #[On('panel.shop.sepidar.party.addresses.assign-data')]
    public function assignData($id): void
    {
        $this->party = Party::findOrFail($id);
        Flux::modal('panel.shop.sepidar.party.addresses.modal')->show();
    }

    #[Computed]
    public function addresses()
    {
        if (!$this->party) {
            return collect();
        }

        return PartyAddress::query()
            ->where('PartyRef', $this->party->PartyId)
            ->get();
    }

    public function render()
    {
        return view('livewire.panel.shop.sepidar.party.addresses');
    }
}
