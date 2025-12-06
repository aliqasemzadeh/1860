<?php

namespace App\Livewire\ServiceCenter\Repair;

use App\Models\ServiceCenter\Repair;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

class Logs extends Component
{
    public Repair $repair;

    public int $id;

    #[On('service-center.repair.logs.assign-data')]
    public function assignData(int $id): void
    {
        $this->repair = Repair::findOrFail($id);
        $this->id = $this->repair->id;

        Flux::modal('service-center.repair.logs.modal')->show();
    }
    public function render()
    {
        return view('livewire.service-center.repair.logs');
    }
}
