<x-slot name="title">
    {{ __('app.shopping_cart') }}
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
                    <span class="text-zinc-900 dark:text-zinc-100 font-medium">{{ __('app.shopping_cart') }}</span>
                </nav>

                {{-- Page Header --}}
                <div class="mb-8">
                    <flux:heading size="xl">{{ __('app.shopping_cart') }}</flux:heading>
                    <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('app.shopping_cart_description') }}</flux:text>
                </div>

                @if($this->cartItems && $this->cartItems->count() > 0)
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {{-- Cart Items --}}
                        <div class="lg:col-span-2 space-y-4">
                            @foreach($this->cartItems as $item)
                                @if($item->itemable)
                                    <div class="flex gap-4 p-6 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
                                        {{-- Product Image --}}
                                        @if(method_exists($item->itemable, 'file_path') && $item->itemable->file_path)
                                            <div class="flex-shrink-0">
                                                <a href="{{ $item->itemable->url }}">
                                                    <img
                                                        src="{{ \Illuminate\Support\Facades\Storage::url($item->itemable->file_path) }}"
                                                        alt="{{ product_image_alt($item->itemable->name ?? '') }}"
                                                        class="w-24 h-24 object-cover rounded-lg border border-zinc-200 dark:border-zinc-700"
                                                    />
                                                </a>
                                            </div>
                                        @endif

                                        {{-- Product Info --}}
                                        <div class="flex-1 min-w-0">
                                            <a href="{{ $item->itemable->url }}">
                                                <flux:heading size="md" class="mb-2 text-zinc-900 dark:text-zinc-100 hover:text-zinc-600 dark:hover:text-zinc-400 transition-colors">
                                                    {{ $item->itemable->name ?? __('app.product') }}
                                                </flux:heading>
                                            </a>

                                            {{-- Options (Color, Warranty) --}}
                                            @if($item->options)
                                                @php
                                                    $options = is_string($item->options) ? json_decode($item->options, true) : $item->options;
                                                @endphp
                                                <div class="flex flex-wrap gap-4 mb-2">
                                                    @if(isset($options['color']))
                                                        <div class="flex items-center gap-2">
                                                            <div
                                                                class="w-5 h-5 rounded-full border border-zinc-300 dark:border-zinc-600"
                                                                style="background-color: {{ $options['color']['hex'] ?? '#000' }};"
                                                            ></div>
                                                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                                                {{ $options['color']['name'] ?? '' }}
                                                            </flux:text>
                                                        </div>
                                                    @endif
                                                    @if(isset($options['warranty']))
                                                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                                            {{ __('app.warranty') }}: {{ $options['warranty']['name'] ?? '' }}
                                                        </flux:text>
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- Price --}}
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
                                            <div class="mt-3">
                                                <flux:text class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                                    {{ number_format($itemPrice * $item->quantity, 0) }} {{ __('general.toman') }}
                                                </flux:text>
                                                @if($item->quantity > 1)
                                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-500 block mt-1">
                                                        {{ number_format($itemPrice, 0) }} {{ __('general.toman') }} × {{ $item->quantity }}
                                                    </flux:text>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Quantity Controls --}}
                                        <div class="flex flex-col items-end justify-between gap-4">
                                            <button
                                                type="button"
                                                wire:click="removeItem({{ $item->id }})"
                                                class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors p-2"
                                                title="{{ __('app.remove_item') }}"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                            <div class="flex items-center gap-3">
                                                <button
                                                    type="button"
                                                    wire:click="decreaseQuantity({{ $item->id }})"
                                                    class="w-10 h-10 flex items-center justify-center border border-zinc-300 dark:border-zinc-600 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                    </svg>
                                                </button>
                                                <span class="w-12 text-center text-base font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $item->quantity }}
                                                </span>
                                                <button
                                                    type="button"
                                                    wire:click="increaseQuantity({{ $item->id }})"
                                                    class="w-10 h-10 flex items-center justify-center border border-zinc-300 dark:border-zinc-600 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        {{-- Order Summary --}}
                        <div class="lg:col-span-1">
                            <div class="sticky top-4 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-6">
                                <flux:heading size="lg" class="mb-6">{{ __('app.order_summary') }}</flux:heading>

                                <div class="space-y-4 mb-6">
                                    <div class="flex items-center justify-between">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('app.subtotal') }}</flux:text>
                                        <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ number_format($this->totalAmount, 0) }} {{ __('general.toman') }}
                                        </flux:text>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('app.shipping') }}</flux:text>
                                        <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ __('app.calculated_at_checkout') }}
                                        </flux:text>
                                    </div>
                                    <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                                        <div class="flex items-center justify-between">
                                            <flux:heading size="md" class="text-zinc-900 dark:text-zinc-100">
                                                {{ __('app.total') }}
                                            </flux:heading>
                                            <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                                                {{ number_format($this->totalAmount, 0) }} {{ __('general.toman') }}
                                            </flux:heading>
                                        </div>
                                    </div>
                                </div>

                                <flux:button 
                                    href="{{ route('order.checkout') }}" 
                                    variant="primary" 
                                    class="w-full"
                                    wire:navigate
                                >
                                    {{ __('app.proceed_to_checkout') }}
                                </flux:button>

                                <flux:button 
                                    href="{{ route('home') }}" 
                                    variant="ghost" 
                                    class="w-full mt-3"
                                    wire:navigate
                                >
                                    {{ __('app.continue_shopping') }}
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-12">
                        <div class="text-center">
                            <flux:icon.shopping-cart class="w-24 h-24 mx-auto text-zinc-400 dark:text-zinc-600 mb-6" />
                            <flux:heading size="xl" class="mb-4">{{ __('app.cart_is_empty') }}</flux:heading>
                            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">
                                {{ __('app.cart_is_empty_description') }}
                            </flux:text>
                            <flux:button href="{{ route('home') }}" variant="primary" wire:navigate>
                                {{ __('app.continue_shopping') }}
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
                        <flux:heading size="xl" class="mb-4">{{ __('app.please_login') }}</flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">
                            {{ __('app.please_login_to_view_cart') }}
                        </flux:text>
                        <flux:button href="{{ route('login') }}" variant="primary" wire:navigate>
                            {{ __('app.login') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </section>
    @endauth
</div>
