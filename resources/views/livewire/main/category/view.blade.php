@php
use Illuminate\Support\Facades\Storage;

$hasActiveFilters = $this->brandId
    || $this->minPrice
    || $this->maxPrice
    || $this->stockFilter !== 'available'
    || $this->sortBy !== 'created_at';
@endphp

<x-slot name="head">
    <x-seo :seo="$this->seo" />
</x-slot>

<div>
    <section class="py-8 antialiased md:py-12">
        <div class="mx-auto max-w-7xl px-4 2xl:px-0">
            {{-- Category Header --}}
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-4">
                    @if($this->category->icon)
                        <div class="text-4xl">
                            {!! $this->category->icon !!}
                        </div>
                    @endif
                    <div>
                        <flux:heading size="xl" class="mb-2">
                            {{ $this->category->name }}
                        </flux:heading>
                        @if($this->category->children->isNotEmpty())
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach($this->category->children as $child)
                                    <a
                                        href="{{ $child->url }}"
                                        wire:navigate
                                        class="px-3 py-1 text-sm bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded-full transition-colors"
                                    >
                                        {{ $child->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-6" x-data="{ filtersOpen: false }">
                {{-- Mobile filter toggle --}}
                <div class="lg:hidden">
                    <flux:button
                        variant="primary"
                        color="zinc"
                        class="w-full"
                        icon="funnel"
                        x-on:click="filtersOpen = !filtersOpen"
                    >
                        {{ __('app.filter') }}
                    </flux:button>
                </div>

                {{-- Filters sidebar (vertical desktop / dropdown mobile) --}}
                <aside
                    class="w-full lg:w-64 shrink-0 bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 border border-zinc-200 dark:border-zinc-800 space-y-4"
                    x-bind:class="filtersOpen ? 'block' : 'hidden lg:block'"
                >
                    <flux:radio.group
                        wire:model.live="stockFilter"
                        label="{{ __('app.stock_filter') }}"
                    >
                        <flux:radio value="available" label="{{ __('app.stock_filter_available') }}" />
                        <flux:radio value="unavailable" label="{{ __('app.stock_filter_unavailable') }}" />
                        <flux:radio value="all" label="{{ __('app.stock_filter_all') }}" />
                    </flux:radio.group>

                    @if($this->brands->isNotEmpty())
                        <flux:select
                            label="{{ __('general.brand') }}"
                            placeholder="{{ __('app.select_brand') }}"
                            wire:model.live="brandId"
                            searchable
                        >
                            <option value="">{{ __('app.all') }}</option>
                            @foreach($this->brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </flux:select>
                    @endif

                    <div class="space-y-2">
                        <flux:input
                            type="number"
                            label="{{ __('app.min_price') }}"
                            placeholder="0"
                            wire:model.live.debounce.500ms="minPrice"
                        />
                        <flux:input
                            type="number"
                            label="{{ __('app.max_price') }}"
                            placeholder="∞"
                            wire:model.live.debounce.500ms="maxPrice"
                        />
                        <flux:text class="text-xs text-zinc-500">
                            {{ __('general.toman') }}
                        </flux:text>
                    </div>

                    <div>
                        <flux:select
                            label="{{ __('app.sort_by') }}"
                            wire:model.live="sortBy"
                        >
                            <option value="created_at">{{ __('app.sort_newest') }}</option>
                            <option value="name">{{ __('app.sort_name') }}</option>
                            <option value="price_asc">{{ __('app.sort_price_low_high') }}</option>
                            <option value="price_desc">{{ __('app.sort_price_high_low') }}</option>
                        </flux:select>
                    </div>

                    @if($hasActiveFilters)
                        <flux:button
                            variant="ghost"
                            class="w-full"
                            wire:click="clearFilters"
                            icon="x-mark"
                        >
                            {{ __('app.clear_filter') }}
                        </flux:button>
                    @endif
                </aside>

                {{-- Products Grid --}}
                <div class="flex-1 min-w-0">
                    @if($this->products->isEmpty())
                        <div class="flex flex-col items-center justify-center py-16">
                            <flux:heading size="lg" class="text-zinc-500 dark:text-zinc-400 mb-2">
                                {{ __('app.no_products_found') }}
                            </flux:heading>
                            <flux:text class="text-zinc-400 dark:text-zinc-500">
                                {{ __('app.no_products_found_description') }}
                            </flux:text>
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

                                            @if($this->stockFilter === 'all')
                                                <div class="absolute top-2 left-2 text-xs font-bold px-2 py-1 rounded {{ $isAvailable ? 'bg-green-500 text-white' : 'bg-zinc-500 text-white' }}">
                                                    {{ $isAvailable ? __('general.remaining') : __('general.out_of_stock') }}
                                                </div>
                                            @endif
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
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
