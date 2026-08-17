<?php

namespace App\Livewire\Panel\Shop\Product;

use App\Exports\ProductsImportTemplateExport;
use App\Imports\ProductsImport;
use App\Services\Shop\ProductImageSeoService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Import extends Component
{
    use WithFileUploads;

    public $file = null;

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(
            new ProductsImportTemplateExport,
            'products-import-template.xlsx'
        );
    }

    public function import(): void
    {
        $this->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'file.required' => __('general.import_file_required'),
            'file.mimes' => __('general.import_file_invalid'),
        ]);

        $importer = new ProductsImport(app(ProductImageSeoService::class));

        Excel::import($importer, $this->file->getRealPath());

        Flux::modal('panel.shop.product.import.modal')->close();
        $this->reset('file');
        $this->dispatch('panel.shop.product.index.render');

        if ($importer->imported > 0) {
            Flux::toast(
                variant: 'success',
                text: __('general.products_imported', ['count' => number_format($importer->imported)])
            );
        }

        if ($importer->skipped > 0) {
            Flux::toast(
                variant: 'warning',
                text: __('general.products_import_skipped', ['count' => number_format($importer->skipped)])
            );
        }

        if ($importer->imported === 0 && $importer->skipped === 0) {
            Flux::toast(variant: 'danger', text: __('general.products_import_empty'));
        }
    }

    public function render(): View
    {
        return view('livewire.panel.shop.product.import');
    }
}
