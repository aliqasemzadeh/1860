<x-slot name="title">
    {{ __('general.shopping_cart') }}
</x-slot>

<div>
    @auth
        <section class="py-6 antialiased md:py-10">
            <div class="mx-auto max-w-7xl px-4 2xl:px-0">
                {{-- Breadcrumb --}}
                <flux:breadcrumbs class="mb-6">
                    <flux:breadcrumbs.item href="{{ route('home') }}" wire:navigate>{{ __('general.home') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>{{ __('general.shopping_cart') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>

                {{-- Page Header --}}
                <div class="mb-8 flex items-center gap-3">
                    <span class="flex size-11 items-center justify-center rounded-2xl bg-teal-50 text-teal-600 dark:bg-teal-950/50 dark:text-teal-400">
                        <flux:icon.shopping-cart class="size-6" />
                    </span>
                    <div>
                        <flux:heading size="xl">{{ __('general.shopping_cart') }}</flux:heading>
                        <flux:text class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('general.shopping_cart_description') }}</flux:text>
                    </div>
                </div>

                @if($this->cartItems && $this->cartItems->count() > 0)
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:gap-8">
                        {{-- Cart Items --}}
                        <div class="space-y-4 lg:col-span-2">
                            @foreach($this->cartItems as $item)
                                @if($item->itemable)
                                    @php
                                        $options = is_string($item->options) ? json_decode($item->options, true) : $item->options;
                                        $itemPrice = $this->itemPrice($item);
                                        $isActive = (bool) $item->itemable->is_active;
                                    @endphp
                                    <div
                                        wire:key="cart-item-{{ $item->id }}"
                                        class="flex flex-col gap-4 rounded-3xl border border-zinc-200/80 bg-white p-4 shadow-sm transition-shadow hover:shadow-md dark:border-zinc-800/80 dark:bg-zinc-900 sm:p-5 {{ ! $isActive ? 'opacity-75' : '' }}"
                                    >
                                        <div class="flex items-start gap-4">
                                            {{-- Product Image --}}
                                            @if($item->itemable->file_path)
                                                <a href="{{ $item->itemable->url }}" wire:navigate class="shrink-0">
                                                    <img
                                                        src="{{ \Illuminate\Support\Facades\Storage::url($item->itemable->file_path) }}"
                                                        alt="{{ product_image_alt($item->itemable) }}"
                                                        loading="lazy"
                                                        decoding="async"
                                                        class="size-20 rounded-2xl border border-zinc-200 object-cover dark:border-zinc-700 sm:size-24"
                                                    />
                                                </a>
                                            @endif

                                            {{-- Product Info --}}
                                            <div class="min-w-0 flex-1">
                                                <a href="{{ $item->itemable->url }}" wire:navigate>
                                                    <flux:heading size="md" class="mb-2 line-clamp-2 text-sm transition-colors hover:text-teal-600 sm:text-base dark:hover:text-teal-400">
                                                        {{ $item->itemable->name ?? __('general.product') }}
                                                    </flux:heading>
                                                </a>

                                                {{-- Options (Color, Warranty) --}}
                                                @if($options)
                                                    <div class="mb-2 flex flex-wrap items-center gap-x-4 gap-y-1.5">
                                                        @if(isset($options['color']))
                                                            <span class="inline-flex items-center gap-1.5">
                                                                <span
                                                                    class="size-4 rounded-full border border-zinc-300 dark:border-zinc-600"
                                                                    style="background-color: {{ $options['color']['hex'] ?? '#000' }};"
                                                                ></span>
                                                                <flux:text class="text-xs text-zinc-600 dark:text-zinc-400 sm:text-sm">
                                                                    {{ $options['color']['name'] ?? '' }}
                                                                </flux:text>
                                                            </span>
                                                        @endif
                                                        @if(isset($options['warranty']))
                                                            <flux:text class="text-xs text-zinc-600 dark:text-zinc-400 sm:text-sm">
                                                                {{ __('general.warranty') }}: {{ $options['warranty']['name'] ?? '' }}
                                                            </flux:text>
                                                        @endif
                                                    </div>
                                                @endif

                                                @if(! $isActive)
                                                    <flux:badge color="red" size="sm">{{ __('general.out_of_stock') }}</flux:badge>
                                                @else
                                                    <div class="flex items-baseline gap-2">
                                                        <flux:text class="text-base font-black text-zinc-900 dark:text-zinc-100 sm:text-lg">
                                                            {{ number_format($itemPrice * $item->quantity, 0) }} {{ __('general.toman') }}
                                                        </flux:text>
                                                        @if($item->quantity > 1)
                                                            <flux:text class="text-xs text-zinc-500">
                                                                {{ number_format($itemPrice, 0) }} {{ __('general.toman') }} × {{ $item->quantity }}
                                                            </flux:text>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Delete Button --}}
                                            <flux:tooltip content="{{ __('general.remove_item') }}">
                                                <flux:button
                                                    size="sm"
                                                    variant="ghost"
                                                    color="red"
                                                    icon="trash"
                                                    square
                                                    wire:click="removeItem({{ $item->id }})"
                                                    wire:confirm="{{ __('general.are_you_sure') }}"
                                                    aria-label="{{ __('general.remove_item') }}"
                                                />
                                            </flux:tooltip>
                                        </div>

                                        {{-- Quantity Controls --}}
                                        @if($isActive)
                                            <div class="flex items-center justify-between border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                                <flux:text class="text-xs text-zinc-500">{{ __('general.quantity') }}</flux:text>
                                                <div class="flex items-center gap-1 rounded-2xl border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800">
                                                    <flux:button
                                                        size="xs"
                                                        variant="ghost"
                                                        icon="minus"
                                                        square
                                                        wire:click="decreaseQuantity({{ $item->id }})"
                                                        aria-label="-"
                                                    />
                                                    <span class="min-w-8 text-center text-sm font-bold tabular-nums text-zinc-900 dark:text-zinc-100">
                                                        {{ $item->quantity }}
                                                    </span>
                                                    <flux:button
                                                        size="xs"
                                                        variant="ghost"
                                                        icon="plus"
                                                        square
                                                        wire:click="increaseQuantity({{ $item->id }})"
                                                        aria-label="+"
                                                    />
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        {{-- Order Summary --}}
                        <div class="lg:col-span-1">
                            <div class="sticky top-4 rounded-3xl border border-zinc-200/80 bg-white p-5 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900 sm:p-6">
                                <flux:heading size="lg" class="mb-6">{{ __('general.order_summary') }}</flux:heading>

                                <div class="mb-6 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('general.subtotal') }}</flux:text>
                                        <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ number_format($this->totalAmount, 0) }} {{ __('general.toman') }}
                                        </flux:text>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('general.shipping') }}</flux:text>
                                        <flux:text class="text-sm text-zinc-400">
                                            {{ __('general.calculated_at_checkout') }}
                                        </flux:text>
                                    </div>
                                    <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                                        <div class="flex items-center justify-between">
                                            <flux:heading size="md" class="text-zinc-900 dark:text-zinc-100">
                                                {{ __('general.total') }}
                                            </flux:heading>
                                            <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                                                {{ number_format($this->totalAmount, 0) }} {{ __('general.toman') }}
                                            </flux:heading>
                                        </div>
                                    </div>
                                </div>

                                @if($this->hasUnavailableItems)
                                    <flux:text class="mb-3 block text-sm text-red-600 dark:text-red-400">
                                        {{ __('general.remove_unavailable_products_to_continue') }}
                                    </flux:text>
                                    <flux:button variant="primary" class="w-full" disabled>
                                        {{ __('general.proceed_to_checkout') }}
                                    </flux:button>
                                @else
                                    <flux:button
                                        href="{{ route('order.checkout') }}"
                                        variant="primary"
                                        color="teal"
                                        icon="credit-card"
                                        class="w-full"
                                        wire:navigate
                                    >
                                        {{ __('general.proceed_to_checkout') }}
                                    </flux:button>
                                @endif

                                <flux:button
                                    href="{{ route('home') }}"
                                    variant="ghost"
                                    class="mt-3 w-full"
                                    wire:navigate
                                >
                                    {{ __('general.continue_shopping') }}
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-3xl border border-zinc-200/80 bg-white p-12 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900">
                        <div class="text-center">
                            <span class="mx-auto mb-6 flex size-20 items-center justify-center rounded-3xl bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500">
                                <flux:icon.shopping-cart class="size-10" />
                            </span>
                            <flux:heading size="xl" class="mb-4">{{ __('general.cart_is_empty') }}</flux:heading>
                            <flux:text class="mb-8 text-zinc-600 dark:text-zinc-400">
                                {{ __('general.cart_is_empty_description') }}
                            </flux:text>
                            <flux:button href="{{ route('home') }}" variant="primary" color="teal" wire:navigate>
                                {{ __('general.continue_shopping') }}
                            </flux:button>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @else
        <section class="py-8 antialiased md:py-12">
            <div class="mx-auto max-w-7xl px-4 2xl:px-0">
                <div class="rounded-3xl border border-zinc-200/80 bg-white p-12 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900">
                    <div class="text-center">
                        <flux:heading size="xl" class="mb-4">{{ __('general.please_login') }}</flux:heading>
                        <flux:text class="mb-8 text-zinc-600 dark:text-zinc-400">
                            {{ __('general.please_login_to_view_cart') }}
                        </flux:text>
                        <flux:button href="{{ route('login') }}" variant="primary" color="teal" wire:navigate>
                            {{ __('general.login') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </section>
    @endauth
</div>
