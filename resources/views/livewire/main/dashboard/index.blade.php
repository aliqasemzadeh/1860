@php
use Illuminate\Support\Facades\Storage;
@endphp

<x-slot name="title">
    {{ config('app.name') }}
</x-slot>
<div>
    <section class="py-8 antialiased md:py-12">
        <div class="mx-auto max-w-7xl px-4 2xl:px-0">
            <livewire:main.content.box.index :key="'home-brand-boxes'" />

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
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2">
                    @foreach($this->products as $product)
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
                                </div>

                                <div class="p-4 flex flex-col flex-grow items-center">
                                    <flux:heading
                                        size="sm"
                                        class="mb-2 line-clamp-3 min-h-[3rem] text-center group-hover:text-zinc-900 dark:group-hover:text-zinc-100 transition-colors"
                                    >
                                        {{ $product->name }}
                                    </flux:heading>

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
    </section>
</div>
