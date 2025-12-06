<?php

namespace App\Livewire\ServiceCenter\Repair;

use App\Models\ServiceCenter\Repair;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public Repair $repair;

    public int $id;

    #[On('service-center.repair.edit.assign-data')]
    public function assignData(int $id): void
    {
        $this->authorize('service_repair_edit');
        
        $this->repair = Repair::findOrFail($id);
        $this->id = $this->repair->id;

        Flux::modal('service-center.repair.edit.modal')->show();
    }

    public function render()
    {
        return view('livewire.service-center.repair.edit');
    }
}
