<x-slot name="title">
    {{ __('general.shopping_cart') }}
</x-slot>

<div>
    @auth
        <section class="py-8 antialiased md:py-12">
            <div class="mx-auto max-w-7xl px-4 2xl:px-0">
                {{-- Breadcrumb --}}
                <nav class="mb-6 flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                    <a href="{{ route('home') }}" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                        {{ __('general.dashboard') }}
                    </a>
                    <span>/</span>
                    <span class="text-zinc-900 dark:text-zinc-100 font-medium">{{ __('general.shopping_cart') }}</span>
                </nav>

                {{-- Page Header --}}
                <div class="mb-8">
                    <flux:heading size="xl">{{ __('general.shopping_cart') }}</flux:heading>
                    <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('general.shopping_cart_description') }}</flux:text>
                </div>

                @if($this->cartItems && $this->cartItems->count() > 0)
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {{-- Cart Items --}}
                        <div class="lg:col-span-2 space-y-4">
                            @foreach($this->cartItems as $item)
                                @if($item->itemable)
                                    <div class="flex flex-col sm:flex-row gap-4 p-4 sm:p-6 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                                        <div class="flex gap-4 items-start flex-1 min-w-0">
                                            {{-- Product Image --}}
                                            @if(method_exists($item->itemable, 'file_path') && $item->itemable->file_path)
                                                <div class="flex-shrink-0">
                                                    <a href="{{ $item->itemable->url }}">
                                                        <img
                                                            src="{{ \Illuminate\Support\Facades\Storage::url($item->itemable->file_path) }}"
                                                            alt="{{ product_image_alt($item->itemable->name ?? '') }}"
                                                            loading="lazy"
                                                            decoding="async"
                                                            class="size-20 sm:size-24 object-cover rounded-xl border border-zinc-200 dark:border-zinc-700"
                                                        />
                                                    </a>
                                                </div>
                                            @endif

                                            {{-- Product Info --}}
                                            <div class="flex-1 min-w-0">
                                                <a href="{{ $item->itemable->url }}">
                                                    <flux:heading size="md" class="mb-1.5 sm:mb-2 text-sm sm:text-base text-zinc-900 dark:text-zinc-100 hover:text-zinc-600 dark:hover:text-zinc-400 transition-colors line-clamp-2">
                                                        {{ $item->itemable->name ?? __('general.product') }}
                                                    </flux:heading>
                                                </a>

                                                {{-- Options (Color, Warranty) --}}
                                                @if($item->options)
                                                    @php
                                                        $options = is_string($item->options) ? json_decode($item->options, true) : $item->options;
                                                    @endphp
                                                    <div class="flex flex-wrap gap-2.5 sm:gap-4 mb-2">
                                                        @if(isset($options['color']))
                                                            <div class="flex items-center gap-1.5">
                                                                <div
                                                                    class="size-4 sm:size-5 rounded-full border border-zinc-300 dark:border-zinc-600"
                                                                    style="background-color: {{ $options['color']['hex'] ?? '#000' }};"
                                                                ></div>
                                                                <flux:text class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-400">
                                                                    {{ $options['color']['name'] ?? '' }}
                                                                </flux:text>
                                                            </div>
                                                        @endif
                                                        @if(isset($options['warranty']))
                                                            <flux:text class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-400">
                                                                {{ __('general.warranty') }}: {{ $options['warranty']['name'] ?? '' }}
                                                            </flux:text>
                                                        @endif
                                                    </div>
                                                @endif

                                                {{-- Price (Desktop) --}}
                                                <div class="hidden sm:block">
                                                    @if(! $item->itemable->is_active)
                                                        <flux:badge color="red" size="sm">{{ __('general.out_of_stock') }}</flux:badge>
                                                    @else
                                                        @php
                                                            $itemPrice = $item->itemable->getPrice();
                                                            $options = is_string($item->options) ? json_decode($item->options, true) : $item->options;
                                                            $priceId = $options['price_id'] ?? null;
                                                            if ($priceId) {
                                                                $priceRecord = \App\Models\Shop\ProductPrice::find($priceId);
                                                                if ($priceRecord) {
                                                                    $itemPrice = $priceRecord->sale_price && $priceRecord->sale_price < $priceRecord->price
                                                                        ? $priceRecord->sale_price
                                                                        : $priceRecord->price;
                                                                }
                                                            }
                                                        @endphp
                                                        <div class="mt-2">
                                                            <flux:text class="text-base sm:text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                                                {{ number_format($itemPrice * $item->quantity, 0) }} {{ __('general.toman') }}
                                                            </flux:text>
                                                            @if($item->quantity > 1)
                                                                <flux:text class="text-xs text-zinc-500 block mt-0.5">
                                                                    {{ number_format($itemPrice, 0) }} {{ __('general.toman') }} × {{ $item->quantity }}
                                                                </flux:text>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Delete Button (Mobile) --}}
                                            <button
                                                type="button"
                                                wire:click="removeItem({{ $item->id }})"
                                                class="sm:hidden text-red-500 hover:text-red-700 p-1"
                                                title="{{ __('general.remove_item') }}"
                                            >
                                                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        {{-- Bottom row on Mobile / Right column on Desktop --}}
                                        <div class="flex items-center justify-between sm:flex-col sm:items-end sm:justify-between border-t border-zinc-100 dark:border-zinc-800 pt-3 sm:border-0 sm:pt-0 gap-3">
                                            {{-- Price (Mobile) --}}
                                            <div class="sm:hidden">
                                                @if(! $item->itemable->is_active)
                                                    <flux:badge color="red" size="sm">{{ __('general.out_of_stock') }}</flux:badge>
                                                @else
                                                    @php
                                                        $itemPrice = $item->itemable->getPrice();
                                                        $options = is_string($item->options) ? json_decode($item->options, true) : $item->options;
                                                        $priceId = $options['price_id'] ?? null;
                                                        if ($priceId) {
                                                            $priceRecord = \App\Models\Shop\ProductPrice::find($priceId);
                                                            if ($priceRecord) {
                                                                $itemPrice = $priceRecord->sale_price && $priceRecord->sale_price < $priceRecord->price
                                                                    ? $priceRecord->sale_price
                                                                    : $priceRecord->price;
                                                            }
                                                        }
                                                    @endphp
                                                    <flux:text class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                                        {{ number_format($itemPrice * $item->quantity, 0) }} {{ __('general.toman') }}
                                                    </flux:text>
                                                @endif
                                            </div>

                                            {{-- Delete Button (Desktop) --}}
                                            <button
                                                type="button"
                                                wire:click="removeItem({{ $item->id }})"
                                                class="hidden sm:block text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors p-2"
                                                title="{{ __('general.remove_item') }}"
                                            >
                                                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>

                                            {{-- Quantity Controls --}}
                                            @if($item->itemable->is_active)
                                                <div class="flex items-center gap-2 sm:gap-3 bg-zinc-50 dark:bg-zinc-800 p-1 rounded-xl border border-zinc-200 dark:border-zinc-700">
                                                    <button
                                                        type="button"
                                                        wire:click="decreaseQuantity({{ $item->id }})"
                                                        class="size-8 sm:size-9 flex items-center justify-center rounded-lg hover:bg-white dark:hover:bg-zinc-700 transition-colors"
                                                    >
                                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                        </svg>
                                                    </button>
                                                    <span class="w-8 text-center text-sm sm:text-base font-bold text-zinc-900 dark:text-zinc-100">
                                                        {{ $item->quantity }}
                                                    </span>
                                                    <button
                                                        type="button"
                                                        wire:click="increaseQuantity({{ $item->id }})"
                                                        class="size-8 sm:size-9 flex items-center justify-center rounded-lg hover:bg-white dark:hover:bg-zinc-700 transition-colors"
                                                    >
                                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        {{-- Order Summary --}}
                        <div class="lg:col-span-1">
                            <div class="sticky top-4 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-6">
                                <flux:heading size="lg" class="mb-6">{{ __('general.order_summary') }}</flux:heading>

                                <div class="space-y-4 mb-6">
                                    <div class="flex items-center justify-between">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('general.subtotal') }}</flux:text>
                                        <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ number_format($this->totalAmount, 0) }} {{ __('general.toman') }}
                                        </flux:text>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('general.shipping') }}</flux:text>
                                        <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ __('general.calculated_at_checkout') }}
                                        </flux:text>
                                    </div>
                                    <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
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
                                        class="w-full"
                                        wire:navigate
                                    >
                                        {{ __('general.proceed_to_checkout') }}
                                    </flux:button>
                                @endif

                                <flux:button 
                                    href="{{ route('home') }}" 
                                    variant="ghost" 
                                    class="w-full mt-3"
                                    wire:navigate
                                >
                                    {{ __('general.continue_shopping') }}
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-12">
                        <div class="text-center">
                            <flux:icon.shopping-cart class="w-24 h-24 mx-auto text-zinc-400 dark:text-zinc-600 mb-6" />
                            <flux:heading size="xl" class="mb-4">{{ __('general.cart_is_empty') }}</flux:heading>
                            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">
                                {{ __('general.cart_is_empty_description') }}
                            </flux:text>
                            <flux:button href="{{ route('home') }}" variant="primary" wire:navigate>
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
                <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-12">
                    <div class="text-center">
                        <flux:heading size="xl" class="mb-4">{{ __('general.please_login') }}</flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">
                            {{ __('general.please_login_to_view_cart') }}
                        </flux:text>
                        <flux:button href="{{ route('login') }}" variant="primary" wire:navigate>
                            {{ __('general.login') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </section>
    @endauth
</div>
