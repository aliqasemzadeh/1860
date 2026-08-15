<?php

namespace App\Livewire\Panel\Administrator\SettingManagement\Backup;

use App\Jobs\System\RunBackupJob;
use App\Livewire\Forms\BackupForm;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Create extends Component
{
    public BackupForm $form;

    public function create(): void
    {
        $this->authorize('administrator_setting_backup_create');

        $this->form->validate();

        RunBackupJob::dispatch($this->form->type, $this->form->destination);

        Flux::modal('panel.administrator.setting-management.backup.create.modal')->close();
        $this->dispatch('panel.administrator.setting-management.backup.index.render');
        Flux::toast(__('general.backup_dispatched'));
        $this->form->reset();
    }

    public function render(): View
    {
        return view('livewire.panel.administrator.setting-management.backup.create');
    }
}
