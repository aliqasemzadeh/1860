<x-slot name="title">
    {{ __('general.shipping') }}
</x-slot>

<div>
    <section class="py-8 antialiased md:py-12">
        <div class="mx-auto max-w-7xl px-4 2xl:px-0">
            {{-- Breadcrumb --}}
            <nav class="mb-6 flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                <a href="{{ route('home') }}" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                    {{ __('general.dashboard') }}
                </a>
                <span>/</span>
                <a href="{{ route('order.cart') }}" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                    {{ __('general.shopping_cart') }}
                </a>
                <span>/</span>
                <span class="text-zinc-900 dark:text-zinc-100 font-medium">{{ __('general.shipping') }}</span>
            </nav>

            {{-- Page Header --}}
            <div class="mb-8">
                <flux:heading size="xl">{{ __('general.shipping') }}</flux:heading>
                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('general.shipping_description') }}</flux:text>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Customer Information Section --}}
                    <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-6">
                        <flux:heading size="lg" class="mb-2">{{ __('general.customer_information') }}</flux:heading>
                        <flux:text class="mb-6 text-zinc-600 dark:text-zinc-400">{{ __('general.customer_information_description') }}</flux:text>

                        @if($this->needsProfileCompletion)
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <flux:field>
                                        <flux:label>{{ __('general.first_name') }}</flux:label>
                                        <flux:input wire:model.live="profileForm.first_name" type="text" />
                                        <flux:error name="profileForm.first_name" />
                                    </flux:field>

                                    <flux:field>
                                        <flux:label>{{ __('general.last_name') }}</flux:label>
                                        <flux:input wire:model.live="profileForm.last_name" type="text" />
                                        <flux:error name="profileForm.last_name" />
                                    </flux:field>
                                </div>

                                <flux:field>
                                    <flux:label>{{ __('general.national_code') }}</flux:label>
                                    <flux:input wire:model.live="profileForm.national_code" type="text" maxlength="10" dir="ltr" />
                                    <flux:error name="profileForm.national_code" />
                                </flux:field>
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <flux:text class="text-sm text-zinc-500">{{ __('general.first_name') }}</flux:text>
                                    <flux:text class="font-medium">{{ auth()->user()->first_name }}</flux:text>
                                </div>
                                <div>
                                    <flux:text class="text-sm text-zinc-500">{{ __('general.last_name') }}</flux:text>
                                    <flux:text class="font-medium">{{ auth()->user()->last_name }}</flux:text>
                                </div>
                                <div>
                                    <flux:text class="text-sm text-zinc-500">{{ __('general.national_code') }}</flux:text>
                                    <flux:text class="font-medium" dir="ltr">{{ auth()->user()->national_code }}</flux:text>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Shipping Address Section --}}
                    <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-6">
                        <flux:heading size="lg" class="mb-6">{{ __('general.shipping_address') }}</flux:heading>

                        @if($this->addresses->count() > 0 && !$showNewAddressForm)
                            <div class="space-y-4 mb-4">
                                @foreach($this->addresses as $addr)
                                    <div class="flex items-start gap-4 p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors {{ $selectedAddressId == $addr->id ? 'ring-2 ring-primary-500 border-primary-500' : '' }}">
                                        <label class="flex items-start gap-4 flex-1 cursor-pointer">
                                            <input type="radio" wire:model.live="selectedAddressId" value="{{ $addr->id }}" class="mt-1" />
                                            <div class="flex-1">
                                                <div class="font-medium text-zinc-900 dark:text-zinc-100 mb-1">
                                                    {{ $addr->name ?: __('general.address') }} {{ $addr->is_default ? '('.__('general.default').')' : '' }}
                                                </div>
                                                <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                                    {{ $addr->province_name }}, {{ $addr->city_name }}<br>
                                                    {{ $addr->address }}<br>
                                                    @if($addr->postal_code)
                                                        {{ __('general.postal_code') }}: {{ $addr->postal_code }}<br>
                                                    @endif
                                                    @if($addr->emergency_contact)
                                                        {{ __('general.emergency_contact') }}: {{ $addr->emergency_contact }}
                                                    @endif
                                                </div>
                                            </div>
                                        </label>
                                        <flux:button
                                            size="xs"
                                            variant="danger"
                                            wire:click="deleteAddress({{ $addr->id }})"
                                            wire:confirm="{{ __('general.confirm_delete_address') }}"
                                        >
                                            {{ __('general.delete') }}
                                        </flux:button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($showNewAddressForm)
                            <form wire:submit="saveNewAddress" class="space-y-4">
                                <flux:field>
                                    <flux:label>{{ __('general.address_name') }} <flux:badge variant="ghost" size="sm">{{ __('general.optional') }}</flux:badge></flux:label>
                                    <flux:input wire:model="name" />
                                    <flux:error name="name" />
                                </flux:field>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <flux:field>
                                        <flux:label>{{ __('general.province') }}</flux:label>
                                        <flux:select wire:model.live="province_id" variant="combobox">
                                            <flux:select.option value="">{{ __('general.select_province') }}</flux:select.option>
                                            @foreach($this->provinces as $id => $province)
                                                <flux:select.option value="{{ $id }}">{{ $province }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                        <flux:error name="province_id" />
                                    </flux:field>

                                    <flux:field>
                                        <flux:label>{{ __('general.city') }}</flux:label>
                                        <flux:select wire:model="city_id" variant="combobox">
                                            <flux:select.option value="">{{ __('general.select_city') }}</flux:select.option>
                                            @if(count($this->cities) > 0)
                                                @php
                                                    // Ensure we iterate with string keys preserved
                                                    $citiesArray = is_array($this->cities) ? $this->cities : $this->cities->toArray();
                                                    // Use array_keys to preserve original string keys
                                                    $cityKeys = array_keys($citiesArray);
                                                @endphp
                                                @foreach($cityKeys as $cityKey)
                                                    <flux:select.option value="{{ $cityKey }}">{{ $citiesArray[$cityKey] }}</flux:select.option>
                                                @endforeach
                                            @endif
                                        </flux:select>
                                        <flux:error name="city_id" />
                                    </flux:field>
                                </div>

                                <flux:field>
                                    <flux:label>{{ __('general.address') }}</flux:label>
                                    <flux:textarea wire:model="address" rows="3" />
                                    <flux:error name="address" />
                                </flux:field>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <flux:field>
                                        <flux:label>{{ __('general.postal_code') }}</flux:label>
                                        <flux:input wire:model="postal_code" dir="ltr" />
                                        <flux:error name="postal_code" />
                                    </flux:field>

                                    <flux:field>
                                        <flux:label>{{ __('general.emergency_contact') }} <flux:badge variant="ghost" size="sm">{{ __('general.optional') }}</flux:badge></flux:label>
                                        <flux:input wire:model="emergency_contact" dir="ltr" />
                                        <flux:error name="emergency_contact" />
                                    </flux:field>
                                </div>

                                <flux:field>
                                    <flux:checkbox wire:model="is_default" />
                                    <flux:label>{{ __('general.set_as_default_address') }}</flux:label>
                                </flux:field>

                                <div class="flex gap-3">
                                    <flux:button type="submit" variant="primary">{{ __('general.save') }}</flux:button>
                                    <flux:button type="button" wire:click="toggleNewAddressForm" variant="ghost">{{ __('general.cancel') }}</flux:button>
                                </div>
                            </form>
                        @else
                            <flux:button wire:click="toggleNewAddressForm" variant="ghost" class="w-full">
                                {{ __('general.add_new_address') }}
                            </flux:button>
                        @endif
                    </div>

                    @if($selectedAddressId && count($this->availableShippingMethods) > 0)
                        {{-- Shipping Methods Section --}}
                        <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-6">
                            <flux:heading size="lg" class="mb-6">{{ __('general.shipping_method') }}</flux:heading>

                            <div class="space-y-4">
                                @foreach($this->availableShippingMethods as $method)
                                    <label class="flex items-start gap-4 p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors {{ $selectedShippingRateId == $method['id'] ? 'ring-2 ring-primary-500 border-primary-500' : '' }}">
                                        <input type="radio" wire:model.live="selectedShippingRateId" value="{{ $method['id'] }}" class="mt-1" />
                                        <div class="flex-1">
                                            <div class="font-medium text-zinc-900 dark:text-zinc-100 mb-1">
                                                {{ $method['method_name'] }}
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ number_format($method['cost'], 0) }} {{ __('general.toman') }}
                                                @if($method['estimated_days'])
                                                    • {{ __('general.estimated_delivery') }}: {{ $method['estimated_days'] }}
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            @if($this->isPickupShippingSelected)
                                <flux:callout variant="warning" icon="triangle-alert" class="mt-4" heading="{{ __('general.pickup_shipping_notice') }}">
                                    <flux:text>{{ __('general.pickup_id_documents_required') }}</flux:text>
                                </flux:callout>
                            @endif
                        </div>
                    @endif

                    @if($selectedAddressId && count($this->availableShippingMethods) == 0)
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <flux:text class="text-yellow-800 dark:text-yellow-200">
                                {{ __('general.no_shipping_methods_available') }}
                            </flux:text>
                        </div>
                    @endif

                    {{-- Customer Note --}}
                    <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-6">
                        <flux:heading size="lg" class="mb-4">{{ __('general.customer_note') }}</flux:heading>
                        <flux:field>
                            <flux:textarea wire:model="customerNote" rows="3" placeholder="{{ __('general.customer_note_placeholder') }}" />
                        </flux:field>
                    </div>
                </div>

                {{-- Order Summary Sidebar --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-4 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-6">
                        <flux:heading size="lg" class="mb-6">{{ __('general.order_summary') }}</flux:heading>

                        <div class="space-y-4 mb-6">
                            <div class="flex items-center justify-between">
                                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('general.subtotal') }}</flux:text>
                                <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ number_format($this->subtotal, 0) }} {{ __('general.toman') }}
                                </flux:text>
                            </div>

                            @if($selectedShippingRateId && $this->availableShippingMethods->firstWhere('id', $selectedShippingRateId))
                                @php
                                    $selectedMethod = $this->availableShippingMethods->firstWhere('id', $selectedShippingRateId);
                                @endphp
                                <div class="flex items-center justify-between">
                                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('general.shipping') }}</flux:text>
                                    <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ number_format($selectedMethod['cost'], 0) }} {{ __('general.toman') }}
                                    </flux:text>
                                </div>
                            @else
                                <div class="flex items-center justify-between">
                                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('general.shipping') }}</flux:text>
                                    <flux:text class="font-medium text-zinc-600 dark:text-zinc-400">
                                        {{ __('general.select_shipping_method') }}
                                    </flux:text>
                                </div>
                            @endif

                            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                                <div class="flex items-center justify-between">
                                    <flux:heading size="md" class="text-zinc-900 dark:text-zinc-100">
                                        {{ __('general.total') }}
                                    </flux:heading>
                                    <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                                        @if($selectedShippingRateId && $this->availableShippingMethods->firstWhere('id', $selectedShippingRateId))
                                            @php
                                                $selectedMethod = $this->availableShippingMethods->firstWhere('id', $selectedShippingRateId);
                                                $total = $this->subtotal + $selectedMethod['cost'];
                                            @endphp
                                            {{ number_format($total, 0) }} {{ __('general.toman') }}
                                        @else
                                            {{ number_format($this->subtotal, 0) }} {{ __('general.toman') }}
                                        @endif
                                    </flux:heading>
                                </div>
                            </div>
                        </div>

                        <flux:button
                            wire:click="createOrder"
                            variant="primary"
                            class="w-full"
                            :disabled="!$this->canCompleteOrder"
                        >
                            {{ __('general.complete_order') }}
                        </flux:button>

                        @if(count($this->completeOrderBlockers) > 0)
                            <ul class="mt-3 space-y-1 text-sm text-amber-700 dark:text-amber-300 list-disc list-inside">
                                @foreach($this->completeOrderBlockers as $reason)
                                    <li>{{ $reason }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
