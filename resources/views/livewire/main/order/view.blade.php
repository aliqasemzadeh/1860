<x-slot name="title">
    {{ __('general.order') }} #{{ $this->order->order_number ?? '' }}
</x-slot>

<div>
    @auth
        @if($this->order)
            <section class="py-6 antialiased md:py-10">
                <div class="mx-auto max-w-7xl px-4 2xl:px-0">
                    {{-- Breadcrumb --}}
                    <flux:breadcrumbs class="mb-6">
                        <flux:breadcrumbs.item href="{{ route('home') }}" wire:navigate>{{ __('general.home') }}</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item href="{{ route('order.index') }}" wire:navigate>{{ __('general.my_orders') }}</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item>{{ __('general.order') }} #{{ $this->order->order_number }}</flux:breadcrumbs.item>
                    </flux:breadcrumbs>

                    {{-- Order Header --}}
                    <div class="mb-6 rounded-3xl border border-zinc-200/80 bg-white p-4 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900 sm:p-6">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <div class="mb-2 flex flex-wrap items-center gap-3">
                                    <flux:heading size="xl">
                                        {{ __('general.order') }} #{{ $this->order->order_number }}
                                    </flux:heading>
                                    <flux:badge :color="$this->order->status === 'completed' ? 'emerald' : ($this->order->status === 'cancelled' ? 'red' : 'amber')">
                                        {{ __('general.order_status_' . $this->order->status) }}
                                    </flux:badge>
                                </div>
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ __('general.order_date') }}: {{ jalali($this->order->created_at) }}
                                </flux:text>
                                @if($this->order->tracking_code)
                                    <flux:text class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ __('general.tracking_code') }}: {{ $this->order->tracking_code }}
                                    </flux:text>
                                @endif
                                @if($this->order->shipped_at)
                                    <flux:text class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ __('general.shipped_at') }}: {{ jalali($this->order->shipped_at) }}
                                    </flux:text>
                                @endif
                                @if($this->order->delivered_at)
                                    <flux:text class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ __('general.delivered_at') }}: {{ jalali($this->order->delivered_at) }}
                                    </flux:text>
                                @endif
                            </div>
                            <div class="flex gap-3">
                                @if(!$this->order->paid_at)
                                    <flux:button
                                        href="{{ route('order.payment', ['id' => $this->order->id]) }}"
                                        variant="primary"
                                        color="teal"
                                        icon="credit-card"
                                        wire:navigate
                                    >
                                        {{ __('general.pay_order') }}
                                    </flux:button>
                                @endif
                                <flux:button
                                    href="{{ route('order.index') }}"
                                    variant="ghost"
                                    wire:navigate
                                >
                                    {{ __('general.back_to_orders') }}
                                </flux:button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:gap-8">
                        {{-- Order Items --}}
                        <div class="lg:col-span-2">
                            <div class="rounded-3xl border border-zinc-200/80 bg-white p-4 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900 sm:p-6">
                                <flux:heading size="lg" class="mb-6">{{ __('general.order_items') }}</flux:heading>

                                <div class="space-y-3">
                                    @foreach($this->order->items as $item)
                                        <div
                                            wire:key="order-item-{{ $item->id }}"
                                            class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-zinc-50 p-4 dark:border-zinc-700/80 dark:bg-zinc-800/60"
                                        >
                                            {{-- Item Info --}}
                                            <div class="min-w-0 flex-1">
                                                <flux:heading size="md" class="mb-2 text-sm text-zinc-900 dark:text-zinc-100 sm:text-base">
                                                    {{ $item->name }}
                                                </flux:heading>

                                                <div class="mb-2 flex flex-wrap gap-x-4 gap-y-1.5">
                                                    @if($item->color)
                                                        <span class="inline-flex items-center gap-1.5">
                                                            <span
                                                                class="size-4 rounded-full border border-zinc-300 dark:border-zinc-600"
                                                                style="background-color: {{ $item->color->hex ?? '#000' }};"
                                                            ></span>
                                                            <flux:text class="text-xs text-zinc-600 dark:text-zinc-400 sm:text-sm">
                                                                {{ $item->color->name }}
                                                            </flux:text>
                                                        </span>
                                                    @endif
                                                    @if($item->warranty)
                                                        <flux:text class="text-xs text-zinc-600 dark:text-zinc-400 sm:text-sm">
                                                            {{ __('general.warranty') }}: {{ $item->warranty->name }}
                                                        </flux:text>
                                                    @endif
                                                </div>

                                                <flux:text class="text-xs text-zinc-600 dark:text-zinc-400 sm:text-sm">
                                                    {{ __('general.quantity') }}: {{ $item->quantity }} × {{ number_format($item->unit_price_amount, 0) }} {{ __('general.toman') }}
                                                </flux:text>
                                            </div>

                                            {{-- Item Total --}}
                                            <div class="shrink-0">
                                                <flux:text class="text-base font-black text-zinc-900 dark:text-zinc-100 sm:text-lg">
                                                    {{ number_format($item->total_amount, 0) }} {{ __('general.toman') }}
                                                </flux:text>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Order Summary --}}
                        <div class="lg:col-span-1">
                            <div class="sticky top-4 rounded-3xl border border-zinc-200/80 bg-white p-4 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900 sm:p-6">
                                <flux:heading size="lg" class="mb-6">{{ __('general.order_summary') }}</flux:heading>

                                <div class="mb-6 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('general.subtotal') }}</flux:text>
                                        <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ number_format($this->order->subtotal_amount, 0) }} {{ __('general.toman') }}
                                        </flux:text>
                                    </div>

                                    @if($this->order->discount_amount > 0)
                                        <div class="flex items-center justify-between">
                                            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('general.discount') }}</flux:text>
                                            <flux:text class="font-medium text-emerald-600 dark:text-emerald-400">
                                                -{{ number_format($this->order->discount_amount, 0) }} {{ __('general.toman') }}
                                            </flux:text>
                                        </div>
                                    @endif

                                    @if($this->order->shippingMethod || $this->order->shipping_amount > 0)
                                        @if($this->order->shipping_amount > 0)
                                            <div class="flex items-center justify-between">
                                                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('general.shipping') }}</flux:text>
                                                <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ number_format($this->order->shipping_amount, 0) }} {{ __('general.toman') }}
                                                </flux:text>
                                            </div>
                                        @endif
                                        @if($this->order->shippingMethod)
                                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ $this->order->shippingMethod->name }}
                                            </flux:text>
                                        @endif
                                        @if($this->order->shipping_estimated_days)
                                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ __('general.estimated_delivery') }}: {{ $this->order->shipping_estimated_days }}
                                            </flux:text>
                                        @endif
                                    @endif

                                    @if($this->order->shippingMethod?->isPickup())
                                        <flux:callout variant="warning" icon="triangle-alert" heading="{{ __('general.pickup_shipping_notice') }}">
                                            <flux:text>{{ __('general.pickup_id_documents_required') }}</flux:text>
                                        </flux:callout>
                                    @endif

                                    @if($this->order->tax_amount > 0)
                                        <div class="flex items-center justify-between">
                                            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('general.tax') }}</flux:text>
                                            <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ number_format($this->order->tax_amount, 0) }} {{ __('general.toman') }}
                                            </flux:text>
                                        </div>
                                    @endif

                                    <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                                        <div class="flex items-center justify-between">
                                            <flux:heading size="md" class="text-zinc-900 dark:text-zinc-100">
                                                {{ __('general.total') }}
                                            </flux:heading>
                                            <flux:heading size="lg" class="text-teal-600 dark:text-teal-400">
                                                {{ number_format($this->order->total_amount, 0) }} {{ __('general.toman') }}
                                            </flux:heading>
                                        </div>
                                    </div>
                                </div>

                                {{-- Shipping Address --}}
                                @if($this->order->shipping_address)
                                    @php
                                        $addr = $this->order->shipping_address;
                                        $provinceName = $addr['province_name'] ?? null;
                                        $cityName = $addr['city_name'] ?? null;
                                        if (! $provinceName && isset($addr['province_id'])) {
                                            $addrProvinces = require lang_path('fa/provinces.php');
                                            $provinceName = $addrProvinces[$addr['province_id']] ?? null;
                                        }
                                        if (! $cityName && isset($addr['province_id'], $addr['city_id'])) {
                                            $addrCities = require lang_path('fa/cities.php');
                                            $cityRow = $addrCities[$addr['province_id']][$addr['city_id']] ?? null;
                                            $cityName = is_array($cityRow) ? ($cityRow['name'] ?? null) : $cityRow;
                                        }
                                    @endphp
                                    <div class="mb-6 border-t border-zinc-200 pt-6 dark:border-zinc-700">
                                        <div class="mb-4 flex items-center gap-2">
                                            <flux:icon.map-pin class="size-4 text-zinc-400" />
                                            <flux:heading size="md">{{ __('general.shipping_address') }}</flux:heading>
                                        </div>
                                        <div class="space-y-1 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                                            @if($addr['name'] ?? null)
                                                <div class="font-bold text-zinc-800 dark:text-zinc-100">{{ $addr['name'] }}</div>
                                            @endif
                                            @if($provinceName || $cityName)
                                                <div>{{ implode('، ', array_filter([$provinceName, $cityName])) }}</div>
                                            @endif
                                            @if($addr['address'] ?? null)
                                                <div>{{ $addr['address'] }}</div>
                                            @endif
                                            @if($addr['postal_code'] ?? null)
                                                <div>{{ __('general.postal_code') }}: <span dir="ltr">{{ $addr['postal_code'] }}</span></div>
                                            @endif
                                            @if($addr['emergency_contact'] ?? null)
                                                <div>{{ __('general.emergency_contact') }}: <span dir="ltr">{{ $addr['emergency_contact'] }}</span></div>
                                            @elseif($addr['mobile'] ?? null)
                                                <div>{{ __('general.mobile') }}: <span dir="ltr">{{ $addr['mobile'] }}</span></div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Customer Note --}}
                                @if($this->order->customer_note)
                                    <div class="border-t border-zinc-200 pt-6 dark:border-zinc-700">
                                        <flux:heading size="md" class="mb-4">{{ __('general.customer_note') }}</flux:heading>
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
                <div class="rounded-3xl border border-zinc-200/80 bg-white p-12 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900">
                    <div class="text-center">
                        <flux:heading size="xl" class="mb-4">{{ __('general.please_login') }}</flux:heading>
                        <flux:text class="mb-8 text-zinc-600 dark:text-zinc-400">
                            {{ __('general.please_login_to_view_order') }}
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
