<?php

namespace App\Livewire\ServiceCenter\Repair;

use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ServiceCenter\Repair;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->authorize('service_repair_index');
    }

    #[\Livewire\Attributes\Computed]
    public function repairs()
    {
        return Repair::query()
            ->when($this->search, function ($query) {
                $search = '%' . $this->search . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', $search)
                        ->orWhere('owner_name', 'like', $search)
                        ->orWhere('owner_mobile', 'like', $search)
                        ->orWhere('status', 'like', $search)
                        ->orWhere('device_type', 'like', $search)
                        ->orWhere('device_serial_number', 'like', $search);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[Layout('layouts.panels.service-center')]
    #[On('service-center.repair.index.render')]
    public function render()
    {
        return view('livewire.service-center.repair.index');
    }
}
