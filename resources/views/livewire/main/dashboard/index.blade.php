@php
use Illuminate\Support\Facades\Storage;
@endphp

<x-slot name="title">
    {{ config('app.name') }}
</x-slot>
<div>
    <section class="py-8 antialiased md:py-12">
        <div class="mx-auto max-w-7xl px-4 2xl:px-0">
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
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <flux:heading size="lg">{{ __('app.available_products') }}</flux:heading>
                        <flux:text>{{ __('app.available_products_description') }}</flux:text>
                    </div>

                    <div>
                        <flux:carousel.controls name="available-products" />
                    </div>
                </div>

                <flux:carousel
                    name="available-products"
                    class="-mx-6"
                    :arrows="false"
                    fade
                    track:class="px-6 scroll-px-6"
                >
                    @foreach($this->products as $product)
                        <flux:carousel.slide
                            wire:key="product-{{ $product->id }}"
                            class="w-4/5 sm:w-1/2 md:w-1/3 lg:w-1/4"
                        >
                            <a
                                href="{{ $product->slug ? route('product.view', $product->slug) : route('product.view.id', $product->id) }}"
                                wire:navigate
                                class="group block"
                            >
                                <div class="relative overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                    <img
                                        src="{{ $product->file_path ? Storage::url($product->file_path) : '' }}"
                                        alt="{{ $product->name }}"
                                        class="aspect-2/1 w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    />

                                    @if($product->sale_price && $product->price && $product->sale_price < $product->price)
                                        <div class="absolute top-2 end-2 rounded bg-red-500 px-2 py-1 text-xs font-bold text-white">
                                            {{ __('app.discount') }}
                                        </div>
                                    @endif
                                </div>

                                <flux:heading size="sm" class="mt-2 line-clamp-2 group-hover:text-zinc-900 dark:group-hover:text-zinc-100">
                                    {{ $product->name }}
                                </flux:heading>

                                <div class="mt-1 flex flex-wrap items-center gap-x-1 gap-y-0.5">
                                    @if($product->price)
                                        @if($product->sale_price && $product->sale_price < $product->price)
                                            <flux:text class="text-xs font-bold text-green-600 dark:text-green-400">
                                                {{ number_format($product->sale_price, 0) }} {{ __('app.toman') }}
                                            </flux:text>
                                            <flux:text class="text-xs text-zinc-400 line-through">
                                                {{ number_format($product->price, 0) }}
                                            </flux:text>
                                        @else
                                            <flux:text class="text-xs font-bold text-zinc-800 dark:text-white">
                                                {{ number_format($product->price, 0) }} {{ __('app.toman') }}
                                            </flux:text>
                                        @endif

                                        @if($product->category)
                                            <flux:text class="text-xs text-zinc-500">・</flux:text>
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
                                                {{ $product->category->name }}
                                            </flux:text>
                                        @endif
                                    @endif
                                </div>
                            </a>
                        </flux:carousel.slide>
                    @endforeach
                </flux:carousel>
            @endif
        </div>
    </section>
</div>
