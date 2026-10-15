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

            <div class="flex flex-col lg:flex-row gap-6">
                {{-- Mobile filter trigger --}}
                <div class="lg:hidden mb-2 flex items-center justify-between gap-3">
                    <flux:modal.trigger name="category.filters">
                        <flux:button variant="filled" icon="funnel" class="flex-1 !rounded-xl">
                            {{ __('general.filter') }}
                            @if($hasActiveFilters)
                                <span class="ms-1.5 flex size-2 rounded-full bg-teal-500"></span>
                            @endif
                        </flux:button>
                    </flux:modal.trigger>
                    @if($hasActiveFilters)
                        <flux:button variant="ghost" size="sm" icon="x-mark" wire:click="clearFilters">
                            {{ __('general.clear_filter') }}
                        </flux:button>
                    @endif
                </div>

                {{-- Mobile Filter Flyout Modal --}}
                <flux:modal name="category.filters" flyout position="right" class="w-full max-w-sm lg:hidden">
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <flux:heading size="lg">{{ __('general.filter') }}</flux:heading>
                            @if($hasActiveFilters)
                                <flux:button size="xs" variant="ghost" wire:click="clearFilters">
                                    {{ __('general.clear_filter') }}
                                </flux:button>
                            @endif
                        </div>

                        <flux:radio.group
                            wire:model.live="stockFilter"
                            label="{{ __('general.stock_filter') }}"
                        >
                            <flux:radio value="available" label="{{ __('general.stock_filter_available') }}" />
                            <flux:radio value="unavailable" label="{{ __('general.stock_filter_unavailable') }}" />
                            <flux:radio value="all" label="{{ __('general.stock_filter_all') }}" />
                        </flux:radio.group>

                        @if($this->brands->isNotEmpty())
                            <flux:select
                                label="{{ __('general.brand') }}"
                                placeholder="{{ __('general.select_brand') }}"
                                wire:model.live="brandId"
                                searchable
                            >
                                <option value="">{{ __('general.all') }}</option>
                                @foreach($this->brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </flux:select>
                        @endif

                        <div class="space-y-2">
                            <flux:input
                                type="number"
                                label="{{ __('general.min_price') }}"
                                placeholder="0"
                                wire:model.live.debounce.500ms="minPrice"
                            />
                            <flux:input
                                type="number"
                                label="{{ __('general.max_price') }}"
                                placeholder="∞"
                                wire:model.live.debounce.500ms="maxPrice"
                            />
                            <flux:text class="text-xs text-zinc-500">
                                {{ __('general.toman') }}
                            </flux:text>
                        </div>

                        <div>
                            <flux:select
                                label="{{ __('general.sort_by') }}"
                                wire:model.live="sortBy"
                            >
                                <option value="created_at">{{ __('general.sort_newest') }}</option>
                                <option value="name">{{ __('general.sort_name') }}</option>
                                <option value="price_asc">{{ __('general.sort_price_low_high') }}</option>
                                <option value="price_desc">{{ __('general.sort_price_high_low') }}</option>
                            </flux:select>
                        </div>

                        <flux:button variant="primary" color="teal" class="w-full" x-on:click="$dispatch('modal-close', { name: 'category.filters' })">
                            {{ __('general.save') }}
                        </flux:button>
                    </div>
                </flux:modal>

                {{-- Filters sidebar (Desktop) --}}
                <aside
                    class="hidden lg:block w-64 shrink-0 bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 border border-zinc-200 dark:border-zinc-800 space-y-4"
                >
                    <flux:radio.group
                        wire:model.live="stockFilter"
                        label="{{ __('general.stock_filter') }}"
                    >
                        <flux:radio value="available" label="{{ __('general.stock_filter_available') }}" />
                        <flux:radio value="unavailable" label="{{ __('general.stock_filter_unavailable') }}" />
                        <flux:radio value="all" label="{{ __('general.stock_filter_all') }}" />
                    </flux:radio.group>

                    @if($this->brands->isNotEmpty())
                        <flux:select
                            label="{{ __('general.brand') }}"
                            placeholder="{{ __('general.select_brand') }}"
                            wire:model.live="brandId"
                            searchable
                        >
                            <option value="">{{ __('general.all') }}</option>
                            @foreach($this->brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </flux:select>
                    @endif

                    <div class="space-y-2">
                        <flux:input
                            type="number"
                            label="{{ __('general.min_price') }}"
                            placeholder="0"
                            wire:model.live.debounce.500ms="minPrice"
                        />
                        <flux:input
                            type="number"
                            label="{{ __('general.max_price') }}"
                            placeholder="∞"
                            wire:model.live.debounce.500ms="maxPrice"
                        />
                        <flux:text class="text-xs text-zinc-500">
                            {{ __('general.toman') }}
                        </flux:text>
                    </div>

                    <div>
                        <flux:select
                            label="{{ __('general.sort_by') }}"
                            wire:model.live="sortBy"
                        >
                            <option value="created_at">{{ __('general.sort_newest') }}</option>
                            <option value="name">{{ __('general.sort_name') }}</option>
                            <option value="price_asc">{{ __('general.sort_price_low_high') }}</option>
                            <option value="price_desc">{{ __('general.sort_price_high_low') }}</option>
                        </flux:select>
                    </div>

                    @if($hasActiveFilters)
                        <flux:button
                            variant="ghost"
                            class="w-full"
                            wire:click="clearFilters"
                            icon="x-mark"
                        >
                            {{ __('general.clear_filter') }}
                        </flux:button>
                    @endif
                </aside>

                {{-- Products Grid --}}
                <div class="flex-1 min-w-0">
                    @if($this->products->isEmpty())
                        <div class="flex flex-col items-center justify-center py-16">
                            <flux:heading size="lg" class="text-zinc-500 dark:text-zinc-400 mb-2">
                                {{ __('general.no_products_found') }}
                            </flux:heading>
                            <flux:text class="text-zinc-400 dark:text-zinc-500">
                                {{ __('general.no_products_found_description') }}
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
                                    <flux:card class="h-full flex flex-col p-1.5 sm:p-2 rounded-2xl">
                                        <div class="relative aspect-square w-full overflow-hidden bg-zinc-100 dark:bg-zinc-800 rounded-xl">
                                            @if($product->file_path)
                                                <img
                                                    src="{{ Storage::url($product->file_path) }}"
                                                    alt="{{ product_image_alt($product) }}"
                                                    loading="lazy"
                                                    decoding="async"
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                />
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-zinc-400">
                                                    <svg class="w-12 h-12 sm:w-16 sm:h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            @endif

                                            @if($product->sale_price && $product->price && $product->sale_price < $product->price)
                                                <div class="absolute top-2 right-2 bg-red-500 text-white text-[10px] sm:text-xs font-bold px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-md">
                                                    {{ __('general.discount') }}
                                                </div>
                                            @endif

                                            @if($this->stockFilter === 'all')
                                                <div class="absolute top-2 left-2 text-[10px] sm:text-xs font-bold px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-md {{ $isAvailable ? 'bg-green-500 text-white' : 'bg-zinc-500 text-white' }}">
                                                    {{ $isAvailable ? __('general.remaining') : __('general.out_of_stock') }}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="p-2 sm:p-4 flex flex-col flex-grow items-center text-center">
                                            <flux:heading
                                                size="sm"
                                                class="mb-1.5 sm:mb-2 line-clamp-2 min-h-[2.5rem] sm:min-h-[3rem] text-xs sm:text-sm font-semibold group-hover:text-zinc-900 dark:group-hover:text-zinc-100 transition-colors"
                                            >
                                                {{ $product->name }}
                                            </flux:heading>

                                            @if($product->brand)
                                                <flux:text class="text-[11px] sm:text-xs text-zinc-500 dark:text-zinc-400 mb-1.5 sm:mb-2 truncate max-w-full">
                                                    {{ $product->brand->name }}
                                                </flux:text>
                                            @endif

                                            <div class="mt-auto pt-2.5 sm:pt-3 border-t border-zinc-100 dark:border-zinc-700/70 w-full">
                                                @if($product->price)
                                                    <div class="flex items-center justify-center gap-1.5 sm:gap-2">
                                                        @if($product->sale_price && $product->sale_price < $product->price)
                                                            <div class="flex flex-col items-center">
                                                                <div class="text-sm sm:text-base md:text-lg font-bold text-green-600 dark:text-green-400">
                                                                    {{ number_format($product->sale_price, 0) }} <span class="text-[10px] sm:text-xs font-medium">{{ __('general.toman') }}</span>
                                                                </div>
                                                                <div class="text-[11px] sm:text-xs text-zinc-400 dark:text-zinc-500 line-through">
                                                                    {{ number_format($product->price, 0) }}
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="text-sm sm:text-base md:text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                                                {{ number_format($product->price, 0) }} <span class="text-[10px] sm:text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('general.toman') }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="text-xs sm:text-sm text-zinc-400 dark:text-zinc-500 text-center">
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
