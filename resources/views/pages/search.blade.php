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

@php
    use Illuminate\Support\Facades\Storage;
@endphp

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
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-2">
                @foreach($this->products as $product)
                    @php
                        $isAvailable = ($product->default_price['available'] ?? false) === true;
                    @endphp
                    <a
                        href="{{ $product->url }}"
                        wire:navigate
                        wire:key="product-{{ $product->id }}"
                        class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1 overflow-hidden flex flex-col h-full"
                    >
                        <flux:card class="h-full flex flex-col p-1">
                            <div class="relative aspect-square w-full overflow-hidden bg-zinc-100 dark:bg-zinc-800 rounded-t-lg">
                                @if($product->file_path)
                                    <img
                                        src="{{ Storage::url($product->file_path) }}"
                                        alt="{{ product_image_alt($product) }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    />
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-zinc-400">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif

                                @if($product->sale_price && $product->price && $product->sale_price < $product->price)
                                    <div class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                                        {{ __('general.discount') }}
                                    </div>
                                @endif

                                <div class="absolute top-2 left-2 text-xs font-bold px-2 py-1 rounded {{ $isAvailable ? 'bg-green-500 text-white' : 'bg-zinc-500 text-white' }}">
                                    {{ $isAvailable ? __('general.remaining') : __('general.out_of_stock') }}
                                </div>
                            </div>

                            <div class="p-4 flex flex-col flex-grow items-center">
                                <flux:heading
                                    size="sm"
                                    class="mb-2 line-clamp-3 min-h-[3rem] text-center group-hover:text-zinc-900 dark:group-hover:text-zinc-100 transition-colors"
                                >
                                    {{ $product->name }}
                                </flux:heading>

                                @if($product->brand)
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 mb-2">
                                        {{ $product->brand->name }}
                                    </flux:text>
                                @endif

                                <div class="mt-auto pt-3 border-t border-zinc-200 dark:border-zinc-700 w-full">
                                    @if($product->price)
                                        <div class="flex items-center justify-center gap-2">
                                            @if($product->sale_price && $product->sale_price < $product->price)
                                                <div class="flex flex-col items-center">
                                                    <div class="text-lg font-bold text-green-600 dark:text-green-400">
                                                        {{ number_format($product->sale_price, 0) }} {{ __('general.toman') }}
                                                    </div>
                                                    <div class="text-sm text-zinc-400 dark:text-zinc-500 line-through">
                                                        {{ number_format($product->price, 0) }} {{ __('general.toman') }}
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                                    {{ number_format($product->price, 0) }} {{ __('general.toman') }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-sm text-zinc-400 dark:text-zinc-500 text-center">
                                            {{ __('general.price_not_available') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </flux:card>
                    </a>
                @endforeach
            </div>

            <div class="mt-12">
                {{ $this->products->links() }}
            </div>
        @endif
    </div>
</div>
