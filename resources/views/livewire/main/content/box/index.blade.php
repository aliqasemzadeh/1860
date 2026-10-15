@php
    use Illuminate\Support\Facades\Storage;
@endphp
<div class="space-y-10 md:space-y-14">
    @foreach($this->boxes as $box)
        @php
            $theme = $box->color_theme ?? [];
            $accent = $theme['accent'] ?? ($theme['bg'] ?? '#0d9488');
            $logoUrl = $box->getFirstMediaUrl('box_images');
            $boxUrl = route('content.box.view', ['id' => $box->id, 'slug' => $box->title_en]);
        @endphp

        <section
            wire:key="brand-box-{{ $box->id }}"
            class="group/box relative overflow-hidden rounded-[2rem] border border-zinc-200/80 bg-white/70 shadow-sm backdrop-blur-xl dark:border-zinc-800/80 dark:bg-zinc-900/60"
            style="--brand: {{ $accent }};"
        >
            {{-- Ambient brand glow --}}
            <div class="pointer-events-none absolute -top-32 -end-32 size-80 rounded-full opacity-30 blur-3xl transition-opacity duration-700 group-hover/box:opacity-50 dark:opacity-20" style="background: var(--brand);"></div>
            <div class="pointer-events-none absolute -bottom-40 -start-20 size-72 rounded-full opacity-15 blur-3xl dark:opacity-10" style="background: var(--brand);"></div>
            <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-l from-transparent via-[var(--brand)]/60 to-transparent"></div>

            <div class="relative p-5 md:p-8">
                {{-- Box header --}}
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between md:mb-8">
                    <a href="{{ $boxUrl }}" wire:navigate class="group/title flex items-center gap-4">
                        <span class="relative flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-md ring-4 ring-[var(--brand)]/10 transition-transform duration-300 group-hover/title:scale-105 dark:border-zinc-700 md:size-16">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="{{ $box->title_fa }}" class="size-full object-contain p-2" loading="lazy" />
                            @else
                                <span class="text-xl font-black" style="color: var(--brand);">{{ mb_substr($box->title_en ?: $box->title_fa, 0, 1) }}</span>
                            @endif
                        </span>

                        <span class="min-w-0">
                            <span class="flex flex-wrap items-center gap-2">
                                <flux:heading size="xl" class="font-black tracking-tight">{{ $box->title_fa }}</flux:heading>
                                @if($box->title_en)
                                    <span class="rounded-full border border-zinc-200/80 bg-white/80 px-2.5 py-0.5 font-mono text-[11px] font-semibold uppercase tracking-widest text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800/80 dark:text-zinc-400" dir="ltr">
                                        {{ $box->title_en }}
                                    </span>
                                @endif
                            </span>
                            <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('general.brand_tagline_default') }}
                            </flux:text>
                        </span>
                    </a>

                    <a
                        href="{{ $boxUrl }}"
                        wire:navigate
                        class="group/link inline-flex w-fit items-center gap-2 rounded-full border border-zinc-200/80 bg-white/80 py-2 pe-3 ps-4 text-sm font-semibold text-zinc-700 shadow-sm transition-all hover:border-[var(--brand)] hover:text-[var(--brand)] hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800/80 dark:text-zinc-200"
                    >
                        {{ __('general.view_brand_products', ['brand' => $box->title_fa]) }}
                        <span class="flex size-6 items-center justify-center rounded-full bg-zinc-100 transition-all duration-300 group-hover/link:-translate-x-1 group-hover/link:bg-[var(--brand)] group-hover/link:text-white dark:bg-zinc-700">
                            <flux:icon.arrow-left class="size-3.5" />
                        </span>
                    </a>
                </div>

                {{-- Products: snap-scroll on mobile, grid on desktop --}}
                <div class="-mx-5 flex snap-x snap-mandatory gap-4 overflow-x-auto px-5 pb-3 [scrollbar-width:none] md:mx-0 md:grid md:grid-cols-2 md:overflow-visible md:px-0 md:pb-0 lg:grid-cols-4 [&::-webkit-scrollbar]:hidden">
                    @forelse($box->products as $product)
                        @php
                            $price = $product->price;
                            $salePrice = $product->sale_price;
                            $hasDiscount = $price && $salePrice && $salePrice < $price;
                            $discountPercent = $hasDiscount ? (int) round((1 - ($salePrice / $price)) * 100) : 0;
                            $needsVariant = $product->colors_count > 0 || $product->warranties_count > 0;
                        @endphp

                        <article
                            wire:key="brand-box-{{ $box->id }}-product-{{ $product->id }}"
                            class="group/card relative flex w-[72%] shrink-0 snap-start flex-col overflow-hidden rounded-3xl border border-zinc-200/80 bg-white p-3 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl hover:shadow-[var(--brand)]/10 dark:border-zinc-800/80 dark:bg-zinc-900 sm:w-[48%] md:w-auto"
                        >
                            <a href="{{ $product->url }}" wire:navigate class="relative block aspect-square overflow-hidden rounded-2xl bg-gradient-to-b from-zinc-50 to-zinc-100 dark:from-zinc-800 dark:to-zinc-900">
                                <div class="pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-500 group-hover/card:opacity-100" style="background: radial-gradient(circle at 50% 80%, color-mix(in srgb, var(--brand) 25%, transparent), transparent 65%);"></div>

                                @if($product->file_path)
                                    <img
                                        src="{{ Storage::url($product->file_path) }}"
                                        alt="{{ product_image_alt($product) }}"
                                        loading="lazy"
                                        class="relative size-full object-contain p-4 transition-transform duration-500 ease-out group-hover/card:-translate-y-2 group-hover/card:scale-105"
                                    />
                                @else
                                    <div class="flex size-full items-center justify-center text-zinc-300 dark:text-zinc-600">
                                        <flux:icon.image class="size-12" />
                                    </div>
                                @endif

                                <div class="absolute start-2.5 top-2.5 flex flex-col gap-1.5">
                                    @if($hasDiscount)
                                        <flux:badge color="red" size="sm" rounded>{{ $discountPercent }}٪ {{ __('general.discount') }}</flux:badge>
                                    @endif
                                    @if(! $product->in_stock)
                                        <flux:badge color="zinc" size="sm" rounded>{{ __('general.out_of_stock') }}</flux:badge>
                                    @endif
                                </div>
                            </a>

                            <div class="flex flex-grow flex-col gap-2 px-1 pt-3">
                                @if($product->category)
                                    <flux:badge size="sm" color="zinc" inset="top" class="w-fit">{{ $product->category->name }}</flux:badge>
                                @endif

                                <a href="{{ $product->url }}" wire:navigate>
                                    <flux:heading size="sm" class="line-clamp-2 min-h-[2.6rem] leading-snug transition-colors group-hover/card:text-[var(--brand)]">
                                        {{ $product->name }}
                                    </flux:heading>
                                </a>

                                <div class="mt-auto flex items-end justify-between gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                    <div class="flex flex-col leading-tight">
                                        @if($price)
                                            @if($hasDiscount)
                                                <span class="text-[11px] text-zinc-400 line-through dark:text-zinc-500">{{ number_format($price, 0) }}</span>
                                                <span class="text-base font-black text-zinc-900 dark:text-zinc-50">
                                                    {{ number_format($salePrice, 0) }}
                                                    <span class="text-[11px] font-medium text-zinc-500">{{ __('general.toman') }}</span>
                                                </span>
                                            @else
                                                <span class="text-base font-black text-zinc-900 dark:text-zinc-50">
                                                    {{ number_format($price, 0) }}
                                                    <span class="text-[11px] font-medium text-zinc-500">{{ __('general.toman') }}</span>
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-xs text-zinc-400">{{ __('general.price_not_available') }}</span>
                                        @endif
                                    </div>

                                    @if($product->in_stock)
                                        <flux:tooltip content="{{ $needsVariant ? __('general.see_product_details') : __('general.quick_add') }}">
                                            <flux:button
                                                size="sm"
                                                variant="primary"
                                                color="teal"
                                                icon="shopping-bag"
                                                square
                                                wire:click="quickAdd({{ $product->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="quickAdd({{ $product->id }})"
                                                class="!rounded-full shadow-md transition-transform active:scale-90"
                                                aria-label="{{ __('general.quick_add') }}"
                                            />
                                        </flux:tooltip>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full flex items-center justify-center rounded-2xl border border-dashed border-zinc-200 py-10 text-sm text-zinc-500 dark:border-zinc-700 w-full">
                            {{ __('general.no_products_found') }}
                        </div>
                    @endforelse
                </div>

                <p class="mt-3 flex items-center justify-center gap-1.5 text-[11px] text-zinc-400 md:hidden">
                    <flux:icon.chevron-left class="size-3 animate-pulse" />
                    {{ __('general.swipe_to_explore') }}
                </p>
            </div>
        </section>
    @endforeach
</div>
