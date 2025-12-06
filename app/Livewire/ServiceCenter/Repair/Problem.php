<?php

namespace App\Livewire\ServiceCenter\Repair;

use App\Models\ServiceCenter\Repair;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Problem extends Component
{
    public Repair $repair;

    public int $id;

    #[On('service-center.repair.problem.assign-data')]
    public function assignData(int $id): void
    {
        $this->repair = Repair::findOrFail($id);
        $this->id = $this->repair->id;

        Flux::modal('service-center.repair.problem.modal')->show();
        Flux::toast(__('app.problem_loaded'));
    }

    public function render(): View
    {
        return view('livewire.service-center.repair.problem');
    }
}
