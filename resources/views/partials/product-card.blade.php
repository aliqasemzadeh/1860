@php
    /** @var \App\Models\Shop\Product $product */
    $price = $product->price;
    $salePrice = $product->sale_price;
    $hasDiscount = $price && $salePrice && $salePrice < $price;
    $discountPercent = $hasDiscount ? (int) round((1 - ($salePrice / $price)) * 100) : 0;
@endphp

<a
    href="{{ $product->url }}"
    wire:navigate
    class="group block h-full rounded-2xl transition-transform duration-300 hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500"
>
    <flux:card class="h-full flex flex-col p-3 rounded-2xl bg-white dark:bg-zinc-800/90 border border-zinc-200/80 dark:border-zinc-700/70 group-hover:shadow-xl group-hover:border-teal-200 dark:group-hover:border-teal-800/60 transition-all duration-300">
        <div class="relative aspect-square w-full overflow-hidden rounded-xl bg-zinc-50 dark:bg-zinc-900 mb-3">
            @if($product->file_path)
                <img
                    src="{{ \Illuminate\Support\Facades\Storage::url($product->file_path) }}"
                    alt="{{ product_image_alt($product) }}"
                    loading="lazy"
                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
            @else
                <div class="w-full h-full flex items-center justify-center text-zinc-300 dark:text-zinc-600">
                    <flux:icon.image class="size-14" />
                </div>
            @endif

            @if($hasDiscount)
                <div class="absolute top-2 start-2">
                    <flux:badge color="red" size="sm" rounded icon="percent-badge">
                        {{ $discountPercent }}٪ {{ __('general.discount') }}
                    </flux:badge>
                </div>
            @endif
        </div>

        <div class="flex flex-col flex-grow">
            <flux:heading
                size="sm"
                class="line-clamp-2 min-h-[2.75rem] leading-snug text-zinc-900 dark:text-zinc-100 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors"
            >
                {{ $product->name }}
            </flux:heading>

            <div class="mt-auto pt-3 border-t border-zinc-100 dark:border-zinc-700/70 flex items-end justify-between gap-2">
                @if($price)
                    <div class="flex flex-col">
                        @if($hasDiscount)
                            <span class="text-xs text-zinc-400 dark:text-zinc-500 line-through">
                                {{ number_format($price, 0) }}
                            </span>
                            <span class="text-base font-bold text-emerald-600 dark:text-emerald-400">
                                {{ number_format($salePrice, 0) }}
                                <span class="text-xs font-medium">{{ __('general.toman') }}</span>
                            </span>
                        @else
                            <span class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                {{ number_format($price, 0) }}
                                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('general.toman') }}</span>
                            </span>
                        @endif
                    </div>
                @else
                    <flux:badge color="zinc" size="sm" rounded>
                        {{ __('general.price_not_available') }}
                    </flux:badge>
                @endif

                <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 transition-colors group-hover:bg-teal-600 group-hover:text-white dark:bg-zinc-700 dark:text-zinc-300">
                    <flux:icon.arrow-left class="size-4" />
                </span>
            </div>
        </div>
    </flux:card>
</a>
