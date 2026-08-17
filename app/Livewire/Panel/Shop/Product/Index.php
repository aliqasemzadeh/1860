<?php

namespace App\Livewire\Panel\Shop\Product;

use App\Exports\ProductsExport;
use App\Models\Shop\Product;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Index extends Component
{
    use \Livewire\WithPagination;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public string $search = '';

    /** @var array<int> */
    public array $selectedProductIds = [];

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function delete(int $id): void
    {
        $product = Product::query()->find($id);
        if ($product !== null) {
            $product->delete();
            Flux::toast(variant: 'success', text: __('general.product_deleted'));
        }
    }

    public function toggleSelectAllOnPage(): void
    {
        $pageIds = $this->products->getCollection()->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($pageIds === []) {
            return;
        }

        $allSelected = count(array_intersect($pageIds, $this->selectedProductIds)) === count($pageIds);

        if ($allSelected) {
            $this->selectedProductIds = array_values(array_diff($this->selectedProductIds, $pageIds));
        } else {
            $this->selectedProductIds = array_values(array_unique([...$this->selectedProductIds, ...$pageIds]));
        }
    }

    public function openBulkPriceChange(): void
    {
        if ($this->selectedProductIds === []) {
            Flux::toast(variant: 'danger', text: __('general.no_products_selected'));

            return;
        }

        $this->dispatch('panel.shop.product.pricing.bulk-change.assign-data', productIds: $this->selectedProductIds);
    }

    #[On('panel.shop.product.index.clear-selection')]
    public function clearSelection(): void
    {
        $this->selectedProductIds = [];
    }

    public function export(): BinaryFileResponse
    {
        $query = $this->productsQuery();

        if ($this->selectedProductIds !== []) {
            $query->whereIn('id', $this->selectedProductIds);
        }

        $products = $query
            ->with(['category', 'brand', 'unit', 'prices'])
            ->get();

        $filename = 'products-'.now()->format('Y-m-d-His').'.xlsx';

        return Excel::download(new ProductsExport($products), $filename);
    }

    protected function productsQuery(): Builder
    {
        return Product::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->tap(function ($query) {
                if ($this->sortBy) {
                    $query->orderBy($this->sortBy, $this->sortDirection);
                }
            });
    }

    #[Computed]
    public function products(): LengthAwarePaginator
    {
        return $this->productsQuery()
            ->with(['category', 'brand', 'unit'])
            ->paginate(10);
    }

    #[Layout('layouts.panels.shop')]
    #[On('panel.shop.product.index.render')]
    public function render()
    {
        return view('livewire.panel.shop.product.index');
    }
}
