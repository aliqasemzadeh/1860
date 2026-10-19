@php
    use Illuminate\Support\Facades\Storage;
@endphp

<x-slot name="head">
    <x-seo :seo="$this->seo" />
    @if($this->product?->is_active)
        <x-product-schema :product="$this->product" />
    @endif
</x-slot>

<div>
    {{-- Management Modals --}}
    @can('shop_access')
        <livewire:panel.shop.product.edit />
        <livewire:panel.shop.product.colors />
        <livewire:panel.shop.product.warranties />
        <livewire:panel.shop.product.price-fetchers />
        <livewire:panel.shop.product.product-images />
        <livewire:panel.shop.product.product-wizard />
        <livewire:panel.shop.product.product-image-wizard />

        <livewire:panel.shop.setting-management.category.create />
        <livewire:panel.shop.setting-management.brand.create />
        <livewire:panel.shop.setting-management.unit.create />
        <livewire:panel.shop.setting-management.color.create />
        <livewire:panel.shop.setting-management.warranty.create />
    @endcan

    @if($this->product)
        @php
            $product = $this->product;
            $selectedPrice = $this->selectedPrice;
            $isAvailable = $product->is_active && $selectedPrice && $selectedPrice->quantity > 0;
            $hasDiscount = $selectedPrice && $selectedPrice->sale_price && $selectedPrice->price && $selectedPrice->sale_price < $selectedPrice->price;
            $finalPrice = $hasDiscount ? $selectedPrice->sale_price : $selectedPrice?->price;
            $discountPercent = $hasDiscount ? (int) round((1 - ($selectedPrice->sale_price / $selectedPrice->price)) * 100) : 0;
            $savings = $hasDiscount ? $selectedPrice->price - $selectedPrice->sale_price : 0;

            $allImages = $this->allImages;
            $imageUrls = $allImages->map(fn ($img) => Storage::url($img['file_path']))->values()->all();
            $highlights = $this->highlights;
            $specGroups = $this->specGroups;
        @endphp

        <div class="pb-28 antialiased lg:pb-12">
            <div class="mx-auto max-w-7xl px-4 py-6 2xl:px-0 md:py-8">

                {{-- Breadcrumbs --}}
                <flux:breadcrumbs class="mb-6">
                    <flux:breadcrumbs.item href="{{ route('home') }}" wire:navigate icon="home" />
                    @if($product->category)
                        <flux:breadcrumbs.item href="{{ $product->category->url }}" wire:navigate>{{ $product->category->name }}</flux:breadcrumbs.item>
                    @endif
                    <flux:breadcrumbs.item class="max-w-[14rem] truncate sm:max-w-none">{{ $product->name }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>

                {{-- Management toolbar --}}
                @can('shop_access')
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50/80 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900/60">
                        <div class="flex items-center gap-2 text-sm font-semibold text-zinc-600 dark:text-zinc-300">
                            <flux:icon.cog-6-tooth class="size-4" />
                            {{ __('general.options') }}
                            <flux:badge size="sm" :color="$product->is_active ? 'green' : 'red'" inset="top">
                                {{ $product->is_active ? __('general.active') : __('general.inactive') }}
                            </flux:badge>
                        </div>

                        <flux:dropdown position="bottom" align="end">
                            <flux:button size="sm" icon:trailing="chevron-down" color="zinc">{{ __('general.options') }}</flux:button>
                            <flux:menu>
                                <flux:menu.item icon="pencil" wire:click="$dispatch('panel.shop.product.edit.assign-data', { id: '{{ $product->id }}' })">{{ __('general.edit') }}</flux:menu.item>
                                <flux:menu.item icon="palette" wire:click="$dispatch('panel.shop.product.colors.assign-data', { id: '{{ $product->id }}' })">{{ __('general.colors') }}</flux:menu.item>
                                <flux:menu.item icon="shield-check" wire:click="$dispatch('panel.shop.product.warranties.assign-data', { id: '{{ $product->id }}' })">{{ __('general.warranties') }}</flux:menu.item>
                                <flux:menu.item icon="arrow-path" wire:click="$dispatch('panel.shop.product.price-fetchers.assign-data', { id: '{{ $product->id }}' })">{{ __('general.price_fetchers') }}</flux:menu.item>
                                <flux:menu.item icon="image" wire:click="$dispatch('panel.shop.product.images.assign-data', { id: '{{ $product->id }}' })">{{ __('general.images') }}</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item icon="tag" href="{{ route('panel.shop.product.pricing.index', ['productId' => $product->id]) }}" wire:navigate>{{ __('general.pricing') }}</flux:menu.item>
                                <flux:menu.item icon="layers" href="{{ route('panel.shop.product.attributes.index', ['id' => $product->id]) }}" wire:navigate>{{ __('general.product_attributes') }}</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item
                                    :icon="$product->is_active ? 'x' : 'check'"
                                    :variant="$product->is_active ? 'danger' : null"
                                    wire:click="toggleActive"
                                    wire:confirm="{{ $product->is_active ? __('general.confirm_deactivate_product') : __('general.confirm_activate_product') }}"
                                >
                                    {{ $product->is_active ? __('general.deactivate') : __('general.activate') }}
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                @endcan

                {{-- ═══════════════════════ Showcase: gallery + purchase panel ═══════════════════════ --}}
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 lg:gap-10">

                    {{-- Interactive showcase gallery (right side in RTL) --}}
                    <div class="lg:col-span-5 xl:col-span-5">
                        <div
                            class="lg:sticky lg:top-24"
                            x-data="{
                                images: @js($imageUrls),
                                index: 0,
                                loading: false,
                                zoom: false,
                                origin: '50% 50%',
                                canHover: window.matchMedia('(hover: hover)').matches,
                                touchX: null,
                                get current() { return this.images[this.index] ?? null },
                                select(i) {
                                    if (i === this.index || ! this.images[i]) return;
                                    this.loading = true;
                                    const img = new Image();
                                    img.onload = () => { this.index = i; this.loading = false; };
                                    img.onerror = () => { this.loading = false; };
                                    img.src = this.images[i];
                                },
                                next() { if (this.images.length > 1) this.select((this.index + 1) % this.images.length) },
                                prev() { if (this.images.length > 1) this.select((this.index - 1 + this.images.length) % this.images.length) },
                                move(e) {
                                    if (! this.canHover) return;
                                    const r = e.currentTarget.getBoundingClientRect();
                                    const x = Math.min(100, Math.max(0, ((e.clientX - r.left) / r.width) * 100));
                                    const y = Math.min(100, Math.max(0, ((e.clientY - r.top) / r.height) * 100));
                                    this.origin = x.toFixed(1) + '% ' + y.toFixed(1) + '%';
                                },
                                touchStart(e) { this.touchX = e.changedTouches[0].clientX },
                                touchEnd(e) {
                                    if (this.touchX === null) return;
                                    const dx = e.changedTouches[0].clientX - this.touchX;
                                    if (Math.abs(dx) > 40) { dx > 0 ? this.prev() : this.next(); }
                                    this.touchX = null;
                                }
                            }"
                            x-on:keydown.arrow-left.window="next()"
                            x-on:keydown.arrow-right.window="prev()"
                        >
                            {{-- Stage --}}
                            <div
                                class="group/stage relative aspect-square w-full overflow-hidden rounded-[2rem] border border-zinc-200/80 bg-gradient-to-b from-zinc-50 via-white to-zinc-100 shadow-xl shadow-zinc-200/40 dark:border-zinc-800/80 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-950 dark:shadow-black/40"
                                x-on:mousemove="move($event)"
                                x-on:mouseenter="zoom = canHover"
                                x-on:mouseleave="zoom = false"
                                x-on:touchstart.passive="touchStart($event)"
                                x-on:touchend.passive="touchEnd($event)"
                            >
                                {{-- Radial glow behind product --}}
                                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_50%_60%,rgba(20,184,166,0.22),transparent_60%)] transition-opacity duration-700 group-hover/stage:opacity-100 dark:bg-[radial-gradient(circle_at_50%_60%,rgba(45,212,191,0.18),transparent_60%)]"></div>
                                <div class="pointer-events-none absolute inset-x-[15%] bottom-[8%] h-10 rounded-[100%] bg-zinc-900/10 blur-2xl dark:bg-black/60"></div>
                                <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,rgba(0,0,0,0.025)_1px,transparent_1px),linear-gradient(to_bottom,rgba(0,0,0,0.025)_1px,transparent_1px)] bg-[size:2.5rem_2.5rem] [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_70%)] dark:bg-[linear-gradient(to_right,rgba(255,255,255,0.04)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.04)_1px,transparent_1px)]"></div>

                                {{-- Loading shimmer --}}
                                <div x-show="loading" x-transition.opacity class="absolute inset-0 z-20 flex items-center justify-center bg-white/40 backdrop-blur-sm dark:bg-zinc-900/40" style="display: none;">
                                    <flux:icon.loading class="size-8 text-teal-600" />
                                </div>

                                @if(count($imageUrls))
                                    <img
                                        src="{{ $imageUrls[0] }}"
                                        x-bind:src="current"
                                        x-bind:style="{ transformOrigin: origin, transform: zoom ? 'scale(1.9)' : 'scale(1)' }"
                                        x-bind:class="zoom ? 'cursor-zoom-in' : ''"
                                        alt="{{ product_image_alt($product) }}"
                                        class="relative z-10 size-full object-contain p-8 transition-transform duration-300 ease-out will-change-transform md:p-12"
                                        fetchpriority="high"
                                        decoding="async"
                                    />
                                @else
                                    <div class="relative z-10 flex size-full items-center justify-center text-zinc-300 dark:text-zinc-700">
                                        <flux:icon.image class="size-24" />
                                    </div>
                                @endif

                                {{-- Floating badges --}}
                                <div class="absolute start-4 top-4 z-20 flex flex-col gap-2">
                                    @if($product->is_active && $hasDiscount)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-500 px-3 py-1 text-xs font-black text-white shadow-lg shadow-red-500/30">
                                            <flux:icon.flame class="size-3.5" />
                                            {{ $discountPercent }}٪ {{ __('general.discount') }}
                                        </span>
                                    @endif
                                    @if(! $product->is_active || ($selectedPrice && $selectedPrice->quantity <= 0))
                                        <flux:badge color="zinc" rounded>{{ __('general.out_of_stock') }}</flux:badge>
                                    @endif
                                </div>

                                {{-- Zoom hint + gallery trigger --}}
                                <div class="absolute end-4 top-4 z-20 flex flex-col gap-2">
                                    <flux:modal.trigger name="product.gallery">
                                        <flux:tooltip content="{{ __('general.view_gallery') }}">
                                            <flux:button size="sm" variant="filled" icon="expand" square class="!rounded-full bg-white/80 shadow-md backdrop-blur dark:bg-zinc-800/80" aria-label="{{ __('general.view_gallery') }}" />
                                        </flux:tooltip>
                                    </flux:modal.trigger>
                                    <span class="hidden items-center gap-1 rounded-full bg-white/80 px-2.5 py-1 text-[11px] font-medium text-zinc-500 shadow-sm backdrop-blur transition-opacity group-hover/stage:opacity-0 dark:bg-zinc-800/80 dark:text-zinc-400 lg:inline-flex">
                                        <flux:icon.zoom-in class="size-3.5" />
                                        {{ __('general.view') }}
                                    </span>
                                </div>

                                {{-- Prev / next (mobile + desktop) --}}
                                @if(count($imageUrls) > 1)
                                    <div class="absolute inset-x-3 top-1/2 z-20 flex -translate-y-1/2 items-center justify-between opacity-100 transition-opacity lg:opacity-0 lg:group-hover/stage:opacity-100">
                                        <flux:button size="sm" variant="filled" icon="chevron-right" square class="!rounded-full bg-white/80 shadow backdrop-blur dark:bg-zinc-800/80" x-on:click.stop="prev()" aria-label="prev" />
                                        <flux:button size="sm" variant="filled" icon="chevron-left" square class="!rounded-full bg-white/80 shadow backdrop-blur dark:bg-zinc-800/80" x-on:click.stop="next()" aria-label="next" />
                                    </div>

                                    {{-- Dots (mobile) --}}
                                    <div class="absolute inset-x-0 bottom-4 z-20 flex items-center justify-center gap-1.5 lg:hidden">
                                        <template x-for="(img, i) in images" :key="i">
                                            <button type="button" class="h-1.5 rounded-full transition-all duration-300" :class="i === index ? 'w-6 bg-teal-600' : 'w-1.5 bg-zinc-300 dark:bg-zinc-600'" x-on:click="select(i)"></button>
                                        </template>
                                    </div>
                                @endif
                            </div>

                            {{-- Thumbnail strip + gallery button --}}
                            <div class="mt-4 flex items-center gap-3">
                                @if(count($imageUrls) > 1)
                                    <div class="flex min-w-0 flex-1 snap-x gap-2.5 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                        @foreach($imageUrls as $index => $url)
                                            <button
                                                type="button"
                                                wire:key="product-thumb-{{ $index }}"
                                                x-on:click="select({{ $index }})"
                                                class="relative size-16 shrink-0 snap-start overflow-hidden rounded-2xl border-2 bg-white p-1 transition-all duration-300 hover:-translate-y-0.5 dark:bg-zinc-900 md:size-20"
                                                :class="index === {{ $index }} ? 'border-teal-500 shadow-lg shadow-teal-500/20' : 'border-zinc-200 opacity-70 hover:opacity-100 dark:border-zinc-700'"
                                            >
                                                <img src="{{ $url }}" alt="{{ product_image_alt($product) }} - {{ $index + 1 }}" class="size-full object-contain" loading="lazy" decoding="async" />
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex-1"></div>
                                @endif

                                <flux:modal.trigger name="product.gallery">
                                    <flux:button variant="filled" icon="rotate-3d" size="sm" class="shrink-0 !rounded-full">
                                        <span class="hidden sm:inline">{{ __('general.view_gallery') }}</span>
                                    </flux:button>
                                </flux:modal.trigger>
                            </div>

                            {{-- Fullscreen gallery modal (flyout) --}}
                            <flux:modal name="product.gallery" flyout position="right" class="w-full md:max-w-2xl">
                                <div class="flex h-full min-h-0 flex-col gap-4">
                                    <div class="flex items-center justify-between">
                                        <flux:heading size="lg">{{ __('general.product_gallery') }}</flux:heading>
                                        <span class="rounded-full bg-zinc-100 px-3 py-1 font-mono text-xs text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400" dir="ltr">
                                            <span x-text="index + 1"></span> / <span x-text="images.length"></span>
                                        </span>
                                    </div>

                                    <div class="relative min-h-0 flex-1 overflow-hidden rounded-3xl border border-zinc-200/80 bg-gradient-to-b from-zinc-50 to-zinc-100 dark:border-zinc-800 dark:from-zinc-900 dark:to-zinc-950">
                                        @if(count($imageUrls))
                                            <img x-bind:src="current" alt="{{ product_image_alt($product) }}" class="size-full object-contain p-6" />
                                        @endif
                                        @if(count($imageUrls) > 1)
                                            <div class="absolute inset-x-4 top-1/2 flex -translate-y-1/2 items-center justify-between">
                                                <flux:button variant="filled" icon="chevron-right" square class="!rounded-full" x-on:click="prev()" aria-label="prev" />
                                                <flux:button variant="filled" icon="chevron-left" square class="!rounded-full" x-on:click="next()" aria-label="next" />
                                            </div>
                                        @endif
                                    </div>

                                    @if(count($imageUrls) > 1)
                                        <div class="flex shrink-0 gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                            @foreach($imageUrls as $index => $url)
                                                <button type="button" x-on:click="select({{ $index }})" class="size-16 shrink-0 overflow-hidden rounded-xl border-2 bg-white p-1 dark:bg-zinc-900" :class="index === {{ $index }} ? 'border-teal-500' : 'border-zinc-200 dark:border-zinc-700'">
                                                    <img src="{{ $url }}" alt="" class="size-full object-contain" loading="lazy" />
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </flux:modal>
                        </div>
                    </div>

                    {{-- Smart purchase & configuration panel (left side in RTL) --}}
                    <div class="min-w-0 space-y-6 lg:col-span-7">

                        {{-- Identity --}}
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                @if($product->brand)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-zinc-200/80 bg-white py-1 pe-3 ps-1 text-xs font-bold text-zinc-700 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                                        <span class="flex size-6 items-center justify-center rounded-full bg-zinc-900 text-[11px] font-black text-white dark:bg-white dark:text-zinc-900">{{ mb_substr($product->brand->name, 0, 1) }}</span>
                                        {{ $product->brand->name }}
                                    </span>
                                @endif
                                @if($product->category)
                                    <flux:badge size="sm" color="zinc" rounded>{{ $product->category->name }}</flux:badge>
                                @endif
                                @foreach($product->tags->take(2) as $tag)
                                    <flux:badge size="sm" color="teal" rounded>{{ $tag->name }}</flux:badge>
                                @endforeach
                            </div>

                            <flux:heading size="xl" level="1" class="text-2xl font-black leading-snug tracking-tight md:text-3xl">
                                {{ $product->name }}
                            </flux:heading>

                            @if($product->en_name)
                                <p class="font-mono text-sm text-zinc-400 dark:text-zinc-500" dir="ltr">{{ $product->en_name }}</p>
                            @endif

                            <div class="flex flex-wrap items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                                <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-1 font-mono dark:bg-zinc-800" dir="ltr">
                                    <flux:icon.tag class="size-3.5" />
                                    SKU-{{ $product->sku }}
                                </span>
                                @if($isAvailable)
                                    <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-600 dark:text-emerald-400">
                                        <span class="relative flex size-2">
                                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                                        </span>
                                        {{ __('general.in_stock_ready') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Bento spec highlights (2x2) --}}
                        @if($highlights->isNotEmpty())
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($highlights as $i => $highlight)
                                    <div
                                        wire:key="highlight-{{ $i }}"
                                        class="group/hl relative overflow-hidden rounded-2xl border border-zinc-200/80 bg-white p-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-teal-300 hover:shadow-lg hover:shadow-teal-500/10 dark:border-zinc-800/80 dark:bg-zinc-900 dark:hover:border-teal-700"
                                    >
                                        <div class="pointer-events-none absolute -end-6 -top-6 size-20 rounded-full bg-teal-500/5 transition-transform duration-500 group-hover/hl:scale-150 dark:bg-teal-400/5"></div>
                                        <span class="mb-3 flex size-9 items-center justify-center rounded-xl bg-teal-50 text-teal-600 dark:bg-teal-950/60 dark:text-teal-300">
                                            <flux:icon :name="$highlight['icon']" class="size-5" />
                                        </span>
                                        <div class="truncate text-base font-black text-zinc-900 dark:text-zinc-50" title="{{ $highlight['value'] }}">{{ $highlight['value'] }}</div>
                                        <div class="mt-0.5 truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $highlight['label'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Configuration card --}}
                        <div class="overflow-hidden rounded-[1.75rem] border border-zinc-200/80 bg-white/80 shadow-sm backdrop-blur dark:border-zinc-800/80 dark:bg-zinc-900/70">
                            <div class="space-y-6 p-5 md:p-6">

                                {{-- Colors --}}
                                @if($product->colors->isNotEmpty())
                                    <div>
                                        <div class="mb-3 flex items-center justify-between">
                                            <flux:heading size="sm">{{ __('general.product_colors') }}</flux:heading>
                                            @php $selectedColor = $product->colors->firstWhere('id', $this->selectedColorId); @endphp
                                            @if($selectedColor)
                                                <flux:text class="text-xs text-zinc-500">{{ __('general.selected') }}: <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $selectedColor->name }}</span></flux:text>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap gap-3">
                                            @foreach($product->colors as $color)
                                                <flux:tooltip content="{{ $color->name }}">
                                                    <button
                                                        type="button"
                                                        wire:key="color-{{ $color->id }}"
                                                        wire:click="selectColor({{ $color->id }})"
                                                        class="relative flex size-10 items-center justify-center rounded-full ring-2 ring-offset-2 ring-offset-white transition-all duration-200 hover:scale-110 focus:outline-none dark:ring-offset-zinc-900 {{ $this->selectedColorId == $color->id ? 'ring-teal-500 scale-110' : 'ring-transparent hover:ring-zinc-300 dark:hover:ring-zinc-600' }}"
                                                        aria-label="{{ $color->name }}"
                                                    >
                                                        <span class="size-full rounded-full border border-black/10 shadow-inner dark:border-white/10" style="background-color: {{ $color->hex }};"></span>
                                                        @if($this->selectedColorId == $color->id)
                                                            <span class="absolute -bottom-1 -end-1 flex size-4 items-center justify-center rounded-full bg-teal-600 text-white ring-2 ring-white dark:ring-zinc-900">
                                                                <flux:icon.check class="size-2.5" />
                                                            </span>
                                                        @endif
                                                    </button>
                                                </flux:tooltip>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Warranty --}}
                                @if($product->warranties->isNotEmpty())
                                    <div>
                                        <flux:heading size="sm" class="mb-3">{{ __('general.product_warranties') }}</flux:heading>
                                        <flux:radio.group wire:model.live="selectedWarrantyId" variant="pills" class="flex-wrap">
                                            @foreach($product->warranties as $warranty)
                                                <flux:radio wire:key="warranty-{{ $warranty->id }}" :value="$warranty->id" :label="$warranty->name" />
                                            @endforeach
                                        </flux:radio.group>
                                    </div>
                                @endif

                                @if($product->colors->isNotEmpty() || $product->warranties->isNotEmpty())
                                    <flux:separator variant="subtle" />
                                @endif

                                {{-- Price --}}
                                <div>
                                    @if(! $product->is_active)
                                        <div class="flex items-center gap-2 text-lg font-bold text-red-600 dark:text-red-400">
                                            <flux:icon.x class="size-5" />
                                            {{ __('general.out_of_stock') }}
                                        </div>
                                    @elseif($selectedPrice)
                                        <div class="flex flex-wrap items-end justify-between gap-3">
                                            <div>
                                                <div class="mb-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('general.final_price') }}</div>
                                                <div class="flex items-baseline gap-2">
                                                    <span class="text-3xl font-black tracking-tight text-zinc-900 dark:text-zinc-50 md:text-4xl" wire:transition>
                                                        {{ number_format($finalPrice, 0) }}
                                                    </span>
                                                    <span class="text-sm font-semibold text-zinc-500">{{ __('general.toman') }}</span>
                                                </div>
                                                @if($hasDiscount)
                                                    <div class="mt-1 flex items-center gap-2 text-sm">
                                                        <span class="text-zinc-400 line-through">{{ number_format($selectedPrice->price, 0) }}</span>
                                                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">{{ __('general.you_save') }}: {{ number_format($savings, 0) }} {{ __('general.toman') }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            @if($hasDiscount)
                                                <span class="rounded-2xl bg-red-50 px-3 py-2 text-center text-red-600 dark:bg-red-950/50 dark:text-red-400">
                                                    <span class="block text-xl font-black leading-none">{{ $discountPercent }}٪</span>
                                                    <span class="text-[10px] font-semibold">{{ __('general.discount') }}</span>
                                                </span>
                                            @endif
                                        </div>

                                        @if($selectedPrice->quantity > 0 && $selectedPrice->quantity < 10)
                                            <div class="mt-3 flex items-center gap-2 text-xs font-semibold text-orange-600 dark:text-orange-400">
                                                <flux:icon.flame class="size-4" />
                                                {{ __('general.low_stock') }} — {{ number_format($selectedPrice->quantity, 0) }} {{ __('general.remaining') }}
                                            </div>
                                        @elseif($selectedPrice->quantity <= 0)
                                            <div class="mt-3 text-sm font-semibold text-red-600 dark:text-red-400">{{ __('general.out_of_stock') }}</div>
                                        @endif
                                    @else
                                        <flux:text class="text-zinc-400">{{ __('general.price_not_available') }}</flux:text>
                                    @endif
                                </div>

                                {{-- Quantity + CTA --}}
                                @if($isAvailable)
                                    <div class="flex items-stretch gap-3">
                                        <div class="flex shrink-0 items-center rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                                            <flux:button variant="ghost" icon="plus" square wire:click="increaseQuantity" class="!rounded-2xl" aria-label="+" />
                                            <span class="min-w-8 text-center text-sm font-bold tabular-nums" wire:transition>{{ $this->quantity }}</span>
                                            <flux:button variant="ghost" icon="minus" square wire:click="decreaseQuantity" class="!rounded-2xl" aria-label="-" />
                                        </div>

                                        <flux:button
                                            wire:click="addToCart"
                                            wire:loading.attr="disabled"
                                            wire:target="addToCart"
                                            variant="primary"
                                            color="teal"
                                            icon="shopping-cart"
                                            class="group/cta h-auto flex-1 !rounded-2xl py-3 text-base font-bold shadow-lg shadow-teal-500/25 transition-all hover:shadow-xl hover:shadow-teal-500/30 active:scale-[0.98]"
                                        >
                                            <span wire:loading.remove wire:target="addToCart">{{ __('general.add_to_cart') }}</span>
                                            <span wire:loading wire:target="addToCart">...</span>
                                        </flux:button>
                                    </div>
                                @else
                                    <flux:button variant="filled" class="w-full !rounded-2xl py-3" disabled>
                                        {{ __('general.out_of_stock') }}
                                    </flux:button>
                                @endif
                            </div>

                            {{-- Trust & delivery --}}
                            <div class="grid grid-cols-3 divide-x divide-x-reverse divide-zinc-100 border-t border-zinc-100 bg-zinc-50/60 dark:divide-zinc-800 dark:border-zinc-800 dark:bg-zinc-950/40">
                                @foreach([
                                    ['icon' => 'truck', 'title' => __('general.fast_shipping'), 'text' => __('general.fast_shipping_description'), 'dot' => 'bg-teal-500'],
                                    ['icon' => 'shield-check', 'title' => __('general.authenticity_guarantee'), 'text' => __('general.authenticity_guarantee_description'), 'dot' => 'bg-emerald-500'],
                                    ['icon' => 'badge-check', 'title' => __('general.valid_warranty'), 'text' => __('general.valid_warranty_description'), 'dot' => 'bg-sky-500'],
                                ] as $trust)
                                    <div class="flex flex-col items-center gap-1.5 px-2 py-4 text-center">
                                        <span class="relative flex size-9 items-center justify-center rounded-xl bg-white text-zinc-700 shadow-sm ring-1 ring-zinc-200/80 dark:bg-zinc-900 dark:text-zinc-200 dark:ring-zinc-700">
                                            <flux:icon :name="$trust['icon']" class="size-4.5" />
                                            <span class="absolute -end-0.5 -top-0.5 flex size-2.5">
                                                <span class="absolute inline-flex size-full animate-ping rounded-full {{ $trust['dot'] }} opacity-60"></span>
                                                <span class="relative inline-flex size-2.5 rounded-full {{ $trust['dot'] }} ring-2 ring-white dark:ring-zinc-900"></span>
                                            </span>
                                        </span>
                                        <span class="text-[11px] font-bold text-zinc-800 dark:text-zinc-100 md:text-xs">{{ $trust['title'] }}</span>
                                        <span class="hidden text-[10px] leading-4 text-zinc-500 dark:text-zinc-400 sm:block">{{ $trust['text'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Available variants (when more than one priced option exists) --}}
                        @if($product->is_active && $product->prices->where('quantity', '>', 0)->count() > 1)
                            <details class="group rounded-2xl border border-zinc-200/80 bg-white/70 dark:border-zinc-800/80 dark:bg-zinc-900/60">
                                <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-3.5 text-sm font-bold [&::-webkit-details-marker]:hidden">
                                    <span class="flex items-center gap-2">
                                        <flux:icon.layers class="size-4 text-zinc-500" />
                                        {{ __('general.available_variants') }}
                                        <flux:badge size="sm" inset="top">{{ $product->prices->where('quantity', '>', 0)->count() }}</flux:badge>
                                    </span>
                                    <flux:icon.chevron-down class="size-4 text-zinc-400 transition-transform group-open:rotate-180" />
                                </summary>
                                <div class="max-h-64 space-y-2 overflow-y-auto border-t border-zinc-100 p-3 dark:border-zinc-800">
                                    @foreach($product->prices->where('quantity', '>', 0) as $price)
                                        <button
                                            type="button"
                                            wire:key="variant-{{ $price->id }}"
                                            wire:click="selectVariant({{ $price->id }})"
                                            class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/60 {{ $selectedPrice?->id === $price->id ? 'bg-teal-50/70 ring-1 ring-teal-200 dark:bg-teal-950/30 dark:ring-teal-800' : '' }}"
                                        >
                                            <span class="flex min-w-0 items-center gap-2">
                                                @if($price->color)
                                                    <span class="size-4 shrink-0 rounded-full border border-black/10" style="background-color: {{ $price->color->hex }};"></span>
                                                    <span class="truncate text-zinc-700 dark:text-zinc-200">{{ $price->color->name }}</span>
                                                @endif
                                                @if($price->warranty)
                                                    <span class="text-zinc-300 dark:text-zinc-600">•</span>
                                                    <span class="truncate text-zinc-500 dark:text-zinc-400">{{ $price->warranty->name }}</span>
                                                @endif
                                                @if($selectedPrice?->id === $price->id)
                                                    <flux:icon.check class="size-4 shrink-0 text-teal-600 dark:text-teal-400" />
                                                @endif
                                            </span>
                                            <span class="shrink-0 text-end font-bold text-zinc-900 dark:text-zinc-50">
                                                {{ number_format(($price->sale_price && $price->sale_price < $price->price) ? $price->sale_price : $price->price, 0) }}
                                                <span class="text-[10px] font-medium text-zinc-500">{{ __('general.toman') }}</span>
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    </div>
                </div>

                {{-- ═══════════════════════ Deep-dive tabs ═══════════════════════ --}}
                <section class="mt-14 md:mt-20">
                    <flux:tab.group>
                        <flux:tabs variant="segmented" scrollable class="mb-8 w-full md:w-auto">
                            <flux:tab name="review" icon="sparkles" selected class="flex-1 md:flex-none">{{ __('general.expert_review') }}</flux:tab>
                            <flux:tab name="specs" icon="cpu" class="flex-1 md:flex-none">{{ __('general.technical_specifications') }}</flux:tab>
                            <flux:tab name="reviews" icon="message-square" class="flex-1 md:flex-none">{{ __('general.user_reviews') }}</flux:tab>
                        </flux:tabs>

                        {{-- Expert review: visual storytelling --}}
                        <flux:tab.panel name="review" selected class="!p-0">
                            <div class="space-y-12 md:space-y-16">
                                <div class="mx-auto max-w-3xl text-center">
                                    <flux:heading size="xl" class="text-2xl font-black md:text-3xl">
                                        {{ __('general.product_story_title', ['product' => $product->name]) }}
                                    </flux:heading>
                                    <flux:text class="mt-2 text-zinc-500">{{ __('general.product_story_subtitle') }}</flux:text>
                                </div>

                                @if($product->description)
                                    <div class="mx-auto max-w-3xl">
                                        <div class="prose prose-zinc max-w-none leading-8 dark:prose-invert prose-headings:font-black prose-img:rounded-2xl">
                                            {!! $product->description !!}
                                        </div>
                                    </div>
                                @else
                                    <div class="mx-auto max-w-3xl rounded-2xl border border-dashed border-zinc-200 p-8 text-center text-sm text-zinc-400 dark:border-zinc-700">
                                        {{ __('general.empty_description') }}
                                    </div>
                                @endif
                            </div>
                        </flux:tab.panel>

                        {{-- Technical specs: searchable, categorized --}}
                        <flux:tab.panel name="specs" class="!p-0">
                            @if($specGroups->isNotEmpty())
                                <div
                                    x-data="{
                                        q: '',
                                        specs: @js($specGroups->flatten(1)->map(fn ($row) => mb_strtolower($row['label'].' '.$row['value']))->values()->all()),
                                        get term() { return this.q.trim().toLowerCase() },
                                        matches(text) { return this.term === '' || text.includes(this.term) },
                                        get hasMatch() { return this.term === '' || this.specs.some((s) => s.includes(this.term)) }
                                    }"
                                    class="space-y-6"
                                >
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <flux:heading size="lg" class="font-black">{{ __('general.technical_specifications') }}</flux:heading>
                                        <div class="w-full sm:w-72">
                                            <flux:input x-model="q" icon="search" placeholder="{{ __('general.search_in_specs') }}" clearable size="sm" />
                                        </div>
                                    </div>

                                    <div class="grid gap-5 lg:grid-cols-2">
                                        @foreach($specGroups as $groupName => $rows)
                                            <div wire:key="spec-group-{{ $loop->index }}" class="overflow-hidden rounded-3xl border border-zinc-200/80 bg-white dark:border-zinc-800/80 dark:bg-zinc-900">
                                                <div class="flex items-center gap-2 border-b border-zinc-100 bg-zinc-50/70 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-950/40">
                                                    <span class="h-4 w-1 rounded-full bg-teal-500"></span>
                                                    <flux:heading size="sm" class="font-bold">{{ $groupName }}</flux:heading>
                                                </div>
                                                <dl class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                                    @foreach($rows as $row)
                                                        <div
                                                            class="grid grid-cols-5 gap-3 px-5 py-3 text-sm transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40"
                                                            data-spec="{{ mb_strtolower($row['label'].' '.$row['value']) }}"
                                                            x-show="matches($el.dataset.spec)"
                                                        >
                                                            <dt class="col-span-2 text-zinc-500 dark:text-zinc-400">{{ $row['label'] }}</dt>
                                                            <dd class="col-span-3 font-semibold text-zinc-900 dark:text-zinc-100">{{ $row['value'] }}</dd>
                                                        </div>
                                                    @endforeach
                                                </dl>
                                            </div>
                                        @endforeach
                                    </div>

                                    <p x-show="! hasMatch" x-cloak class="rounded-2xl border border-dashed border-zinc-200 p-6 text-center text-sm text-zinc-400 dark:border-zinc-700">
                                        {{ __('general.no_spec_match') }}
                                    </p>
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-zinc-200 py-16 text-center dark:border-zinc-700">
                                    <flux:icon.cpu class="mb-3 size-10 text-zinc-300 dark:text-zinc-600" />
                                    <flux:text class="text-zinc-500">{{ __('general.no_specs_available') }}</flux:text>
                                </div>
                            @endif
                        </flux:tab.panel>

                        {{-- User reviews --}}
                        <flux:tab.panel name="reviews" class="!p-0">
                            <div class="grid gap-6 lg:grid-cols-3">
                                <div class="rounded-3xl border border-zinc-200/80 bg-white p-6 dark:border-zinc-800/80 dark:bg-zinc-900">
                                    <div class="flex items-end gap-3">
                                        <span class="text-5xl font-black tracking-tight text-zinc-900 dark:text-zinc-50">—</span>
                                        <div class="pb-1">
                                            <div class="flex items-center gap-0.5 text-zinc-300 dark:text-zinc-600">
                                                @for($s = 0; $s < 5; $s++)
                                                    <flux:icon.star class="size-4" />
                                                @endfor
                                            </div>
                                            <flux:text class="text-xs text-zinc-500">{{ __('general.no_reviews_yet') }}</flux:text>
                                        </div>
                                    </div>
                                    <div class="mt-5 space-y-2">
                                        @for($s = 5; $s >= 1; $s--)
                                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                                <span class="w-3 tabular-nums">{{ $s }}</span>
                                                <flux:icon.star class="size-3 text-amber-400" />
                                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                                    <div class="h-full w-0 rounded-full bg-amber-400"></div>
                                                </div>
                                                <span class="w-6 text-end tabular-nums">0</span>
                                            </div>
                                        @endfor
                                    </div>
                                </div>

                                <div class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-zinc-200 p-10 text-center dark:border-zinc-700 lg:col-span-2">
                                    <span class="mb-4 flex size-16 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500">
                                        <flux:icon.message-square class="size-8" />
                                    </span>
                                    <flux:heading size="lg" class="mb-1 font-black">{{ __('general.no_reviews_yet') }}</flux:heading>
                                    <flux:text class="max-w-sm text-zinc-500">{{ __('general.no_reviews_yet_description') }}</flux:text>
                                    <flux:text class="mt-3 text-xs text-zinc-400">{{ __('general.reviews_coming_soon') }}</flux:text>
                                </div>
                            </div>
                        </flux:tab.panel>
                    </flux:tab.group>
                </section>

                {{-- ═══════════════════════ Related products ═══════════════════════ --}}
                @if($this->relatedProducts->isNotEmpty())
                    <section class="mt-14 md:mt-20">
                        <div class="mb-5 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="flex size-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-600 dark:bg-teal-950/50 dark:text-teal-400">
                                    <flux:icon.sparkles class="size-6" />
                                </span>
                                <flux:heading size="xl" class="font-extrabold">{{ __('general.related_products') }}</flux:heading>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:carousel.controls name="product-related-carousel" />
                                @if($product->category)
                                    <flux:button href="{{ $product->category->url }}" wire:navigate variant="ghost" size="sm" icon:trailing="chevron-left" class="hidden sm:inline-flex">
                                        {{ __('general.view_all') }}
                                    </flux:button>
                                @endif
                            </div>
                        </div>

                        <flux:carousel name="product-related-carousel" class="-mx-4" :arrows="false" track:class="px-4 scroll-px-4 gap-4 pb-2">
                            @foreach($this->relatedProducts as $related)
                                <flux:carousel.slide wire:key="related-{{ $related->id }}" class="w-[70%] sm:w-1/2 md:w-1/3 lg:w-1/4 xl:w-1/5">
                                    @include('partials.product-card', ['product' => $related])
                                </flux:carousel.slide>
                            @endforeach
                        </flux:carousel>
                    </section>
                @endif

                {{-- ═══════════════════════ Related posts ═══════════════════════ --}}
                @if($this->relatedPosts->isNotEmpty())
                    <section class="mt-14 md:mt-20">
                        <div class="mb-5 flex items-center gap-3">
                            <span class="flex size-10 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                                <flux:icon.newspaper class="size-6" />
                            </span>
                            <flux:heading size="xl" class="font-extrabold">{{ __('general.related_posts') }}</flux:heading>
                        </div>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach($this->relatedPosts as $post)
                                <a href="{{ $post->url }}" wire:navigate wire:key="related-post-{{ $post->id }}" class="group flex flex-col overflow-hidden rounded-3xl border border-zinc-200/80 bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-zinc-800/80 dark:bg-zinc-900">
                                    <div class="relative aspect-[16/10] overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                                        @if($post->featured_image_url)
                                            <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" loading="lazy" decoding="async" class="size-full object-cover transition-transform duration-700 group-hover:scale-110" />
                                        @else
                                            <div class="flex size-full items-center justify-center text-zinc-300 dark:text-zinc-600"><flux:icon.newspaper class="size-10" /></div>
                                        @endif
                                    </div>
                                    <div class="flex flex-grow flex-col gap-2 p-4">
                                        <flux:heading size="sm" class="line-clamp-2 leading-snug transition-colors group-hover:text-indigo-600 dark:group-hover:text-indigo-400">{{ $post->title }}</flux:heading>
                                        @if($post->summary)
                                            <flux:text class="line-clamp-2 text-xs text-zinc-500">{{ $post->summary }}</flux:text>
                                        @endif
                                        <span class="mt-auto inline-flex items-center gap-1 pt-2 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                            {{ __('general.read_article') }}
                                            <flux:icon.arrow-left class="size-3.5 transition-transform group-hover:-translate-x-1" />
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </div>

        {{-- ═══════════════════════ Mobile sticky buy bar ═══════════════════════ --}}
        <div class="fixed inset-x-0 bottom-0 z-50 border-t border-zinc-200/80 bg-white/85 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-[0_-10px_30px_-15px_rgba(0,0,0,0.25)] backdrop-blur-lg dark:border-zinc-800/80 dark:bg-zinc-900/85 lg:hidden">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
                <div class="min-w-0">
                    @if($isAvailable)
                        <div class="flex items-baseline gap-1">
                            <span class="text-lg font-black tabular-nums text-zinc-900 dark:text-zinc-50">{{ number_format($finalPrice, 0) }}</span>
                            <span class="text-[11px] font-semibold text-zinc-500">{{ __('general.toman') }}</span>
                        </div>
                        @if($hasDiscount)
                            <div class="flex items-center gap-1.5 text-[11px]">
                                <span class="text-zinc-400 line-through">{{ number_format($selectedPrice->price, 0) }}</span>
                                <span class="rounded-md bg-red-500 px-1.5 py-0.5 font-bold text-white">{{ $discountPercent }}٪</span>
                            </div>
                        @else
                            <div class="flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                {{ __('general.in_stock') }}
                            </div>
                        @endif
                    @else
                        <span class="text-sm font-bold text-red-600 dark:text-red-400">{{ __('general.out_of_stock') }}</span>
                    @endif
                </div>

                @if($isAvailable)
                    <flux:button
                        wire:click="addToCart"
                        wire:loading.attr="disabled"
                        wire:target="addToCart"
                        variant="primary"
                        color="teal"
                        icon="shopping-cart"
                        class="shrink-0 !rounded-2xl px-5 font-bold shadow-lg shadow-teal-500/25 active:scale-[0.98]"
                    >
                        {{ __('general.add_to_cart') }}
                    </flux:button>
                @else
                    <flux:button variant="filled" class="shrink-0 !rounded-2xl" disabled>{{ __('general.out_of_stock') }}</flux:button>
                @endif
            </div>
        </div>
    @else
        <section class="py-16 antialiased">
            <div class="mx-auto max-w-7xl px-4 2xl:px-0">
                <div class="flex flex-col items-center justify-center py-16">
                    <flux:heading size="lg" class="mb-2 text-zinc-500 dark:text-zinc-400">
                        {{ __('general.product_not_found') }}
                    </flux:heading>
                    <flux:text class="mb-6 text-zinc-400 dark:text-zinc-500">
                        {{ __('general.product_not_found_description') }}
                    </flux:text>
                    <flux:button href="{{ route('home') }}" wire:navigate>
                        {{ __('general.return_to_home') }}
                    </flux:button>
                </div>
            </div>
        </section>
    @endif
</div>
