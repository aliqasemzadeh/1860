<?php

namespace App\Livewire\Panel\User\Address;

use App\Livewire\Concerns\HasProvinceCityOptions;
use App\Livewire\Forms\Panel\User\AddressForm;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Create extends Component
{
    use HasProvinceCityOptions;

    public AddressForm $form;

    public function updatedFormProvinceId(): void
    {
        $this->form->city_id = null;
        unset($this->cities);
    }

    public function create(): void
    {
        $this->authorize('user_address_create');

        $this->form->store();

        Cache::forget('panel.user.dashboard.stats.'.auth()->id());

        Flux::modal('panel.user.address.create.modal')->close();
        $this->dispatch('panel.user.address.index.render');
        Flux::toast(variant: 'success', text: __('app.address_saved'));
        $this->form->reset();
    }

    public function render(): View
    {
        return view('livewire.panel.user.address.create');
    }
}
