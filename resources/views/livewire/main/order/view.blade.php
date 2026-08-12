<x-slot name="title">
    {{ __('app.order') }} #{{ $this->order->order_number ?? '' }}
</x-slot>

<div>
    @auth
        @if($this->order)
            <section class="py-8 antialiased md:py-12">
                <div class="mx-auto max-w-7xl px-4 2xl:px-0">
                    {{-- Breadcrumb --}}
                    <nav class="mb-6 flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                        <a href="{{ route('home') }}" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                            {{ __('app.dashboard') }}
                        </a>
                        <span>/</span>
                        <a href="{{ route('order.index') }}" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                            {{ __('app.my_orders') }}
                        </a>
                        <span>/</span>
                        <span class="text-zinc-900 dark:text-zinc-100 font-medium">{{ __('app.order') }} #{{ $this->order->order_number }}</span>
                    </nav>

                    {{-- Order Header --}}
                    <div class="mb-8 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <flux:heading size="xl">
                                        {{ __('app.order') }} #{{ $this->order->order_number }}
                                    </flux:heading>
                                    <flux:badge variant="{{ $this->order->status === 'completed' ? 'success' : ($this->order->status === 'cancelled' ? 'danger' : 'warning') }}">
                                        {{ __('app.order_status_' . $this->order->status) }}
                                    </flux:badge>
                                </div>
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ __('app.order_date') }}: {{ jalali($this->order->created_at) }}
                                </flux:text>
                                @if($this->order->tracking_code)
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                        {{ __('app.tracking_code') }}: {{ $this->order->tracking_code }}
                                    </flux:text>
                                @endif
                                @if($this->order->shipped_at)
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                        {{ __('app.shipped_at') }}: {{ jalali($this->order->shipped_at) }}
                                    </flux:text>
                                @endif
                                @if($this->order->delivered_at)
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                        {{ __('app.delivered_at') }}: {{ jalali($this->order->delivered_at) }}
                                    </flux:text>
                                @endif
                            </div>
                            <div class="flex gap-3">
                                @if(!$this->order->paid_at)
                                    <flux:button 
                                        href="{{ route('order.payment', ['id' => $this->order->id]) }}" 
                                        variant="primary"
                                        wire:navigate
                                    >
                                        {{ __('app.pay_order') }}
                                    </flux:button>
                                @endif
                                <flux:button 
                                    href="{{ route('order.index') }}" 
                                    variant="ghost"
                                    wire:navigate
                                >
                                    {{ __('app.back_to_orders') }}
                                </flux:button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {{-- Order Items --}}
                        <div class="lg:col-span-2">
                            <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-6">
                                <flux:heading size="lg" class="mb-6">{{ __('app.order_items') }}</flux:heading>
                                
                                <div class="space-y-4">
                                    @foreach($this->order->items as $item)
                                        <div class="flex gap-4 p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                            {{-- Item Info --}}
                                            <div class="flex-1">
                                                <flux:heading size="md" class="mb-2 text-zinc-900 dark:text-zinc-100">
                                                    {{ $item->name }}
                                                </flux:heading>
                                                
                                                <div class="flex flex-wrap gap-4 mb-2">
                                                    @if($item->color)
                                                        <div class="flex items-center gap-2">
                                                            <div
                                                                class="w-4 h-4 rounded-full border border-zinc-300 dark:border-zinc-600"
                                                                style="background-color: {{ $item->color->hex ?? '#000' }};"
                                                            ></div>
                                                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                                                {{ $item->color->name }}
                                                            </flux:text>
                                                        </div>
                                                    @endif
                                                    @if($item->warranty)
                                                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                                            {{ __('app.warranty') }}: {{ $item->warranty->name }}
                                                        </flux:text>
                                                    @endif
                                                </div>

                                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                                    {{ __('app.quantity') }}: {{ $item->quantity }} × {{ number_format($item->unit_price_amount, 0) }} {{ __('app.toman') }}
                                                </flux:text>
                                            </div>

                                            {{-- Item Total --}}
                                            <div class="flex items-center">
                                                <flux:text class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                                    {{ number_format($item->total_amount, 0) }} {{ __('app.toman') }}
                                                </flux:text>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Order Summary --}}
                        <div class="lg:col-span-1">
                            <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-6 sticky top-4">
                                <flux:heading size="lg" class="mb-6">{{ __('app.order_summary') }}</flux:heading>

                                <div class="space-y-4 mb-6">
                                    <div class="flex items-center justify-between">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('app.subtotal') }}</flux:text>
                                        <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ number_format($this->order->subtotal_amount, 0) }} {{ __('app.toman') }}
                                        </flux:text>
                                    </div>
                                    
                                    @if($this->order->discount_amount > 0)
                                        <div class="flex items-center justify-between">
                                            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('app.discount') }}</flux:text>
                                            <flux:text class="font-medium text-green-600 dark:text-green-400">
                                                -{{ number_format($this->order->discount_amount, 0) }} {{ __('app.toman') }}
                                            </flux:text>
                                        </div>
                                    @endif

                                    @if($this->order->shipping_amount > 0)
                                        <div class="flex items-center justify-between">
                                            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('app.shipping') }}</flux:text>
                                            <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ number_format($this->order->shipping_amount, 0) }} {{ __('app.toman') }}
                                            </flux:text>
                                        </div>
                                        @if($this->order->shippingMethod)
                                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ $this->order->shippingMethod->name }}
                                            </flux:text>
                                        @endif
                                        @if($this->order->shipping_estimated_days)
                                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ __('app.estimated_delivery') }}: {{ $this->order->shipping_estimated_days }}
                                            </flux:text>
                                        @endif
                                    @endif

                                    @if($this->order->tax_amount > 0)
                                        <div class="flex items-center justify-between">
                                            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('app.tax') }}</flux:text>
                                            <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ number_format($this->order->tax_amount, 0) }} {{ __('app.toman') }}
                                            </flux:text>
                                        </div>
                                    @endif

                                    <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                                        <div class="flex items-center justify-between">
                                            <flux:heading size="md" class="text-zinc-900 dark:text-zinc-100">
                                                {{ __('app.total') }}
                                            </flux:heading>
                                            <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                                                {{ number_format($this->order->total_amount, 0) }} {{ __('app.toman') }}
                                            </flux:heading>
                                        </div>
                                    </div>
                                </div>

                                {{-- Shipping Address --}}
                                @if($this->order->shipping_address)
                                    <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6 mb-6">
                                        <flux:heading size="md" class="mb-4">{{ __('app.shipping_address') }}</flux:heading>
                                        <div class="space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
                                            @if(isset($this->order->shipping_address['name']))
                                                <div>{{ $this->order->shipping_address['name'] }}</div>
                                            @endif
                                            @if(isset($this->order->shipping_address['address']))
                                                <div>{{ $this->order->shipping_address['address'] }}</div>
                                            @endif
                                            @if(isset($this->order->shipping_address['city']))
                                                <div>{{ $this->order->shipping_address['city'] }}</div>
                                            @endif
                                            @if(isset($this->order->shipping_address['postal_code']))
                                                <div>{{ __('app.postal_code') }}: {{ $this->order->shipping_address['postal_code'] }}</div>
                                            @endif
                                            @if(isset($this->order->shipping_address['mobile']))
                                                <div>{{ __('app.mobile') }}: {{ $this->order->shipping_address['mobile'] }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Customer Note --}}
                                @if($this->order->customer_note)
                                    <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6">
                                        <flux:heading size="md" class="mb-4">{{ __('app.customer_note') }}</flux:heading>
                                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ $this->order->customer_note }}
                                        </flux:text>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    @else
        <section class="py-8 antialiased md:py-12">
            <div class="mx-auto max-w-7xl px-4 2xl:px-0">
                <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-12">
                    <div class="text-center">
                        <flux:heading size="xl" class="mb-4">{{ __('app.please_login') }}</flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">
                            {{ __('app.please_login_to_view_order') }}
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
