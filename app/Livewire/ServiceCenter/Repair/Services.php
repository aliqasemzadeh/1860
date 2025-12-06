<?php

namespace App\Livewire\ServiceCenter\Repair;

use App\Models\ServiceCenter\Repair;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Services extends Component
{
    public Repair $repair;

    public int $id;

    public array $services = [];
    public array $selectedServices = [];

    #[On('service-center.repair.services.assign-data')]
    public function assignData(int $id): void
    {
        $this->repair = Repair::findOrFail($id);
        $this->id = $this->repair->id;

        Flux::modal('service-center.repair.services.modal')->show();
    }

    #[Computed]
    public function services()
    {

    }


    public function render()
    {
        return view('livewire.service-center.repair.services');
    }
}
