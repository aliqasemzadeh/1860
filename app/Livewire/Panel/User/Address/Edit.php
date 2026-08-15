<?php

namespace App\Livewire\Panel\User\Address;

use App\Livewire\Concerns\HasProvinceCityOptions;
use App\Livewire\Forms\Panel\User\AddressForm;
use App\Models\Customer\ShippingAddress;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    use HasProvinceCityOptions;

    public AddressForm $form;

    public ?ShippingAddress $address = null;

    public function updatedFormProvinceId(): void
    {
        $this->form->city_id = null;
        unset($this->cities);
    }

    #[On('panel.user.address.edit.assign-data')]
    public function assignData(int $id): void
    {
        $this->authorize('user_address_edit');

        $this->address = auth()->user()->shippingAddresses()->findOrFail($id);
        $this->form->setAddress($this->address);
        unset($this->cities);

        Flux::modal('panel.user.address.edit.modal')->show();
    }

    public function edit(): void
    {
        $this->authorize('user_address_edit');

        if (! $this->address) {
            return;
        }

        $this->form->update($this->address);

        Cache::forget('panel.user.dashboard.stats.'.auth()->id());

        Flux::modal('panel.user.address.edit.modal')->close();
        $this->dispatch('panel.user.address.index.render');
        Flux::toast(variant: 'success', text: __('general.address_updated'));
    }

    public function render(): View
    {
        return view('livewire.panel.user.address.edit');
    }
}
