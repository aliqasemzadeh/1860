<?php

namespace App\Livewire\Panel\User\Address;

use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
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

    public function mount(): void
    {
        $this->authorize('user_address_index');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function addresses(): LengthAwarePaginator
    {
        return auth()->user()
            ->shippingAddresses()
            ->when($this->search, function ($query) {
                $search = '%'.$this->search.'%';
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', $search)
                        ->orWhere('address', 'like', $search)
                        ->orWhere('postal_code', 'like', $search);
                });
            })
            ->orderByDesc('is_default')
            ->when(
                $this->sortBy,
                fn ($query) => $query->orderBy($this->sortBy, $this->sortDirection),
                fn ($query) => $query->orderByDesc('created_at')
            )
            ->paginate(10);
    }

    public function delete(int $id): void
    {
        $this->authorize('user_address_delete');

        $address = auth()->user()->shippingAddresses()->find($id);

        if (! $address) {
            return;
        }

        $address->delete();

        Cache::forget('panel.user.dashboard.stats.'.auth()->id());
        $this->dispatch('panel.user.address.index.render');
        Flux::toast(variant: 'success', text: __('app.address_deleted'));
    }

    public function setDefault(int $id): void
    {
        $this->authorize('user_address_edit');

        $address = auth()->user()->shippingAddresses()->find($id);

        if (! $address) {
            return;
        }

        DB::transaction(function () use ($address) {
            auth()->user()->shippingAddresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        Cache::forget('panel.user.dashboard.stats.'.auth()->id());
        $this->dispatch('panel.user.address.index.render');
        Flux::toast(variant: 'success', text: __('app.default_address_updated'));
    }

    #[Layout('layouts.panels.user')]
    #[On('panel.user.address.index.render')]
    public function render()
    {
        $this->authorize('user_address_index');

        return view('livewire.panel.user.address.index');
    }
}
