<?php

namespace App\Livewire\Panel\User\Dashboard;

use App\Livewire\Forms\Panel\User\ProfileForm;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public ProfileForm $form;

    #[On('panel.user.dashboard.edit.assign-data')]
    public function assignData(): void
    {
        $this->authorize('user_profile_update');

        $this->form->setUser(auth()->user());
        Flux::modal('panel.user.dashboard.edit.modal')->show();
    }

    public function save(): void
    {
        $this->authorize('user_profile_update');

        $this->form->update(auth()->user());

        Cache::forget('panel.user.dashboard.stats.'.auth()->id());

        Flux::modal('panel.user.dashboard.edit.modal')->close();
        $this->dispatch('panel.user.dashboard.index.render');
        Flux::toast(variant: 'success', text: __('general.profile_updated'));
    }

    public function render(): View
    {
        return view('livewire.panel.user.dashboard.edit');
    }
}
