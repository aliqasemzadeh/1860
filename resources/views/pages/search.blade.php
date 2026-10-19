<?php

use App\Models\Shop\Product;
use App\Support\Seo\Seo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url]
    public string $q = '';

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function products(): LengthAwarePaginator
    {
        return Product::query()
            ->active()
            ->with(['category', 'brand', 'prices' => function ($query) {
                $query->orderByDesc('is_default')
                    ->orderByDesc('created_at');
            }])
            ->search($this->q)
            ->orderByAvailability()
            ->orderBy('name')
            ->paginate(24);
    }

    #[Computed]
    public function seo(): Seo
    {
        $term = trim($this->q);
        $page = max(1, (int) request()->query('page', 1));

        $title = $term !== ''
            ? __('general.search_results_for', ['query' => $term])
            : __('general.search_results');

        if ($page > 1) {
            $title .= ' | '.__('general.seo_page', ['page' => $page]);
        }

        $params = array_filter([
            'q' => $term !== '' ? $term : null,
            'page' => $page > 1 ? $page : null,
        ]);

        return new Seo(
            title: $title,
            description: Seo::clean($title),
            canonical: route('search.index', $params),
            noindex: true,
        );
    }
};

?>

<x-slot name="head">
    <x-seo :seo="$this->seo" />
</x-slot>

<div>
    <div class="mx-auto max-w-7xl px-4 2xl:px-0 py-6 md:py-10">
        <flux:breadcrumbs class="mb-6">
            <flux:breadcrumbs.item href="{{ route('home') }}" wire:navigate>{{ __('general.home') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ __('general.search_results') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div class="relative mb-10 overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-50 via-white to-zinc-100 p-6 md:p-10 dark:from-zinc-900 dark:via-zinc-900/90 dark:to-zinc-950 border border-zinc-200/70 dark:border-zinc-800 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-3 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200/80 dark:border-teal-800/60">
                        <flux:icon.search size="xs" />
                        <span>{{ __('general.search') }}</span>
                    </div>
                    <flux:heading size="2xl" level="1">
                        @if(trim($q) !== '')
                            {{ __('general.search_results_for', ['query' => $q]) }}
                        @else
                            {{ __('general.search_results') }}
                        @endif
                    </flux:heading>
                    @if(trim($q) !== '' && $this->products->total() > 0)
                        <flux:subheading size="lg" class="text-zinc-600 dark:text-zinc-300">
                            {{ __('general.search_results_count', ['count' => number_format($this->products->total())]) }}
                        </flux:subheading>
                    @endif
                </div>

                <div class="w-full md:w-80">
                    <flux:input
                        wire:model.live.debounce.300ms="q"
                        icon="search"
                        placeholder="{{ __('general.search_in_products') }}"
                        clearable
                    />
                </div>
            </div>
        </div>

        @if(trim($q) === '')
            <div class="py-20 flex flex-col items-center justify-center text-center">
                <div class="h-16 w-16 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 dark:text-zinc-500 mb-4 shadow-sm">
                    <flux:icon.search size="lg" />
                </div>
                <flux:heading size="lg" class="mb-2">{{ __('general.search_in_products') }}</flux:heading>
                <flux:subheading class="text-zinc-500">{{ __('general.search_placeholder') }}</flux:subheading>
            </div>
        @elseif($this->products->isEmpty())
            <div class="py-20 flex flex-col items-center justify-center text-center">
                <div class="h-16 w-16 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 dark:text-zinc-500 mb-4 shadow-sm">
                    <flux:icon.search-x size="lg" />
                </div>
                <flux:heading size="lg" class="mb-2">{{ __('general.no_results') }}</flux:heading>
                <flux:subheading class="text-zinc-500">{{ __('general.no_products_found_description') }}</flux:subheading>
            </div>
        @else
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-2 md:grid-cols-3 md:gap-4 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                @foreach($this->products as $product)
                    <div wire:key="product-{{ $product->id }}">
                        @include('partials.product-card', ['product' => $product])
                    </div>
                @endforeach
            </div>

            <div class="mt-12">
                {{ $this->products->links() }}
            </div>
        @endif
    </div>
</div>
