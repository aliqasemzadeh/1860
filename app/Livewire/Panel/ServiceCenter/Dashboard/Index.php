<?php

namespace App\Livewire\Panel\ServiceCenter\Dashboard;

use App\Enums\StatusEnum;
use App\Models\ServiceCenter\Repair;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('service_center_dashboard_index');
    }

    public function sortItem($item, $position)
    {

        Flux::toast('Done');
    }

    #[Computed]
    public function repairs(): Collection
    {
        return Repair::where('status', StatusEnum::New->value)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[Layout('layouts.panels.service-center')]
    public function render()
    {
        return view('livewire.panel.service-center.dashboard.index');
    }
}
