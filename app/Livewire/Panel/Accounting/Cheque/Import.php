<?php

namespace App\Livewire\Panel\Accounting\Cheque;

use App\Imports\CheuesImport;
use App\Models\Accounting\Cheque;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class Import extends Component
{
    use WithFileUploads;

    public $file;

    public function import(): void
    {
        $this->authorize('accounting_cheque_import');

        $this->validate([
            'file' => ['required', 'file'],
        ]);

        // Remove all previous cheques before importing new data
        Cheque::truncate();

        Excel::import(new CheuesImport(), $this->file);

        Flux::toast(variant: 'success', text: __('app.cheques_imported'));

        $this->reset('file');
        $this->dispatch('accounting.cheque.index.render');
        Flux::modal('accounting.cheque.import.modal')->close();
    }

    public function render()
    {
        return view('livewire.panel.accounting.cheque.import');
    }
}
