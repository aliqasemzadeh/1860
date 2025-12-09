<?php

namespace App\Livewire\ServiceCenter\Repair;

use App\Models\ServiceCenter\Repair;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->authorize('service_center_repair_index');
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    #[\Livewire\Attributes\Computed]
    public function repairs()
    {
        return Repair::query()
            ->when($this->search, function ($query) {
                $search = '%'.$this->search.'%';
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', $search)
                        ->orWhere('owner_name', 'like', $search)
                        ->orWhere('owner_mobile', 'like', $search)
                        ->orWhere('status', 'like', $search)
                        ->orWhere('device_type', 'like', $search)
                        ->orWhere('owner_organization', 'like', $search)
                        ->orWhere('device_serial_number', 'like', $search);
                });
            })
            ->tap(function ($query) {
                if ($this->sortBy !== '') {
                    $query->orderBy($this->sortBy, $this->sortDirection);
                }
            })
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
