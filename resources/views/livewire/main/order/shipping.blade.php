<x-slot name="title">
    {{ __('app.shipping') }}
</x-slot>

<div>
    <section class="py-8 antialiased md:py-12">
        <div class="mx-auto max-w-7xl px-4 2xl:px-0">
            {{-- Breadcrumb --}}
            <nav class="mb-6 flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                <a href="{{ route('home') }}" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                    {{ __('app.dashboard') }}
                </a>
                <span>/</span>
                <a href="{{ route('order.cart') }}" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                    {{ __('app.shopping_cart') }}
                </a>
                <span>/</span>
                <span class="text-zinc-900 dark:text-zinc-100 font-medium">{{ __('app.shipping') }}</span>
            </nav>

            {{-- Page Header --}}
            <div class="mb-8">
                <flux:heading size="xl">{{ __('app.shipping') }}</flux:heading>
                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('app.shipping_description') }}</flux:text>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Shipping Address Section --}}
                    <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-6">
                        <flux:heading size="lg" class="mb-6">{{ __('app.shipping_address') }}</flux:heading>

                        @if($this->addresses->count() > 0 && !$showNewAddressForm)
                            <div class="space-y-4 mb-4">
                                @foreach($this->addresses as $addr)
                                    <label class="flex items-start gap-4 p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors {{ $selectedAddressId == $addr->id ? 'ring-2 ring-primary-500 border-primary-500' : '' }}">
                                        <input type="radio" wire:model.live="selectedAddressId" value="{{ $addr->id }}" class="mt-1" />
                                        <div class="flex-1">
                                            <div class="font-medium text-zinc-900 dark:text-zinc-100 mb-1">
                                                {{ $addr->name ?: __('app.address') }} {{ $addr->is_default ? '('.__('app.default').')' : '' }}
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ $addr->province_name }}, {{ $addr->city_name }}<br>
                                                {{ $addr->address }}<br>
                                                @if($addr->postal_code)
                                                    {{ __('app.postal_code') }}: {{ $addr->postal_code }}<br>
                                                @endif
                                                @if($addr->emergency_contact)
                                                    {{ __('app.emergency_contact') }}: {{ $addr->emergency_contact }}
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        @if($showNewAddressForm)
                            <form wire:submit="saveNewAddress" class="space-y-4">
                                <flux:field>
                                    <flux:label>{{ __('app.address_name') }} <flux:badge variant="ghost" size="sm">{{ __('app.optional') }}</flux:badge></flux:label>
                                    <flux:input wire:model="name" />
                                    <flux:error name="name" />
                                </flux:field>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <flux:field>
                                        <flux:label>{{ __('app.province') }}</flux:label>
                                        <flux:select wire:model.live="province_id" variant="combobox">
                                            <flux:select.option value="">{{ __('app.select_province') }}</flux:select.option>
                                            @foreach($this->provinces as $id => $province)
                                                <flux:select.option value="{{ $id }}">{{ $province }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                        <flux:error name="province_id" />
                                    </flux:field>

                                    <flux:field>
                                        <flux:label>{{ __('app.city') }}</flux:label>
                                        <flux:select wire:model="city_id" variant="combobox" :disabled="!$province_id">
                                            <flux:select.option value="">{{ __('app.select_city') }}</flux:select.option>
                                            @if($province_id && count($this->cities) > 0)
                                                @foreach($this->cities as $index => $city)
                                                    <flux:select.option value="{{ $index }}">{{ $city }}</flux:select.option>
                                                @endforeach
                                            @endif
                                        </flux:select>
                                        <flux:error name="city_id" />
                                    </flux:field>
                                </div>

                                <flux:field>
                                    <flux:label>{{ __('app.address') }}</flux:label>
                                    <flux:textarea wire:model="address" rows="3" />
                                    <flux:error name="address" />
                                </flux:field>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <flux:field>
                                        <flux:label>{{ __('app.postal_code') }} <flux:badge variant="ghost" size="sm">{{ __('app.optional') }}</flux:badge></flux:label>
                                        <flux:input wire:model="postal_code" dir="ltr" />
                                        <flux:error name="postal_code" />
                                    </flux:field>

                                    <flux:field>
                                        <flux:label>{{ __('app.emergency_contact') }} <flux:badge variant="ghost" size="sm">{{ __('app.optional') }}</flux:badge></flux:label>
                                        <flux:input wire:model="emergency_contact" dir="ltr" />
                                        <flux:error name="emergency_contact" />
                                    </flux:field>
                                </div>

                                <flux:field>
                                    <flux:checkbox wire:model="is_default" />
                                    <flux:label>{{ __('app.set_as_default_address') }}</flux:label>
                                </flux:field>

                                <div class="flex gap-3">
                                    <flux:button type="submit" variant="primary">{{ __('app.save') }}</flux:button>
                                    <flux:button type="button" wire:click="toggleNewAddressForm" variant="ghost">{{ __('app.cancel') }}</flux:button>
                                </div>
                            </form>
                        @else
                            <flux:button wire:click="toggleNewAddressForm" variant="ghost" class="w-full">
                                {{ __('app.add_new_address') }}
                            </flux:button>
                        @endif
                    </div>

                    @if($selectedAddressId && count($this->availableShippingMethods) > 0)
                        {{-- Shipping Methods Section --}}
                        <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-6">
                            <flux:heading size="lg" class="mb-6">{{ __('app.shipping_method') }}</flux:heading>

                            <div class="space-y-4">
                                @foreach($this->availableShippingMethods as $method)
                                    <label class="flex items-start gap-4 p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors {{ $selectedShippingRateId == $method['id'] ? 'ring-2 ring-primary-500 border-primary-500' : '' }}">
                                        <input type="radio" wire:model.live="selectedShippingRateId" value="{{ $method['id'] }}" class="mt-1" />
                                        <div class="flex-1">
                                            <div class="font-medium text-zinc-900 dark:text-zinc-100 mb-1">
                                                {{ $method['method_name'] }}
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ number_format($method['cost'], 0) }} {{ __('app.toman') }}
                                                @if($method['estimated_days'])
                                                    • {{ __('app.estimated_delivery') }}: {{ $method['estimated_days'] }}
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($selectedAddressId && count($this->availableShippingMethods) == 0)
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <flux:text class="text-yellow-800 dark:text-yellow-200">
                                {{ __('app.no_shipping_methods_available') }}
                            </flux:text>
                        </div>
                    @endif

                    {{-- Customer Note --}}
                    <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-6">
                        <flux:heading size="lg" class="mb-4">{{ __('app.customer_note') }}</flux:heading>
                        <flux:field>
                            <flux:textarea wire:model="customerNote" rows="3" placeholder="{{ __('app.customer_note_placeholder') }}" />
                        </flux:field>
                    </div>
                </div>

                {{-- Order Summary Sidebar --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-4 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-6">
                        <flux:heading size="lg" class="mb-6">{{ __('app.order_summary') }}</flux:heading>

                        <div class="space-y-4 mb-6">
                            <div class="flex items-center justify-between">
                                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('app.subtotal') }}</flux:text>
                                <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ number_format($this->subtotal, 0) }} {{ __('app.toman') }}
                                </flux:text>
                            </div>

                            @if($selectedShippingRateId && $this->availableShippingMethods->firstWhere('id', $selectedShippingRateId))
                                @php
                                    $selectedMethod = $this->availableShippingMethods->firstWhere('id', $selectedShippingRateId);
                                @endphp
                                <div class="flex items-center justify-between">
                                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('app.shipping') }}</flux:text>
                                    <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ number_format($selectedMethod['cost'], 0) }} {{ __('app.toman') }}
                                    </flux:text>
                                </div>
                            @else
                                <div class="flex items-center justify-between">
                                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('app.shipping') }}</flux:text>
                                    <flux:text class="font-medium text-zinc-600 dark:text-zinc-400">
                                        {{ __('app.select_shipping_method') }}
                                    </flux:text>
                                </div>
                            @endif

                            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                                <div class="flex items-center justify-between">
                                    <flux:heading size="md" class="text-zinc-900 dark:text-zinc-100">
                                        {{ __('app.total') }}
                                    </flux:heading>
                                    <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                                        @if($selectedShippingRateId && $this->availableShippingMethods->firstWhere('id', $selectedShippingRateId))
                                            @php
                                                $selectedMethod = $this->availableShippingMethods->firstWhere('id', $selectedShippingRateId);
                                                $total = $this->subtotal + $selectedMethod['cost'];
                                            @endphp
                                            {{ number_format($total, 0) }} {{ __('app.toman') }}
                                        @else
                                            {{ number_format($this->subtotal, 0) }} {{ __('app.toman') }}
                                        @endif
                                    </flux:heading>
                                </div>
                            </div>
                        </div>

                        <flux:button 
                            wire:click="createOrder"
                            variant="primary" 
                            class="w-full"
                            :disabled="!$selectedAddressId || !$selectedShippingRateId"
                        >
                            {{ __('app.complete_order') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
