<x-slot name="title">
    {{ __('general.shipping') }}
</x-slot>

<div>
    <section class="py-6 antialiased md:py-10">
        <div class="mx-auto max-w-7xl px-4 2xl:px-0">
            {{-- Breadcrumb --}}
            <flux:breadcrumbs class="mb-6">
                <flux:breadcrumbs.item href="{{ route('home') }}" wire:navigate>{{ __('general.home') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('order.cart') }}" wire:navigate>{{ __('general.shopping_cart') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.shipping') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            {{-- Checkout steps --}}
            <div class="mb-8 flex items-center justify-center gap-2 sm:gap-3" dir="rtl">
                <div class="flex items-center gap-2">
                    <span class="flex size-8 items-center justify-center rounded-full bg-teal-600 text-white shadow-sm shadow-teal-500/30">
                        <flux:icon.check class="size-4" />
                    </span>
                    <span class="hidden text-xs font-bold text-teal-600 sm:inline dark:text-teal-400">{{ __('general.shopping_cart') }}</span>
                </div>
                <span class="h-0.5 w-8 rounded-full bg-teal-500 sm:w-16"></span>
                <div class="flex items-center gap-2">
                    <span class="flex size-8 items-center justify-center rounded-full bg-teal-600 text-white shadow-sm shadow-teal-500/30 ring-4 ring-teal-100 dark:ring-teal-950">
                        <flux:icon.truck class="size-4" />
                    </span>
                    <span class="text-xs font-black text-zinc-900 sm:text-sm dark:text-zinc-50">{{ __('general.shipping') }}</span>
                </div>
                <span class="h-0.5 w-8 rounded-full bg-zinc-200 sm:w-16 dark:bg-zinc-700"></span>
                <div class="flex items-center gap-2">
                    <span class="flex size-8 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500">
                        <flux:icon.credit-card class="size-4" />
                    </span>
                    <span class="hidden text-xs font-bold text-zinc-400 sm:inline dark:text-zinc-500">{{ __('general.payment') }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:gap-8">
                {{-- Main Content --}}
                <div class="space-y-6 lg:col-span-2">
                    {{-- Customer Information Section --}}
                    <div class="rounded-3xl border border-zinc-200/80 bg-white p-4 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900 sm:p-6">
                        <div class="mb-6 flex items-center gap-3">
                            <span class="flex size-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-600 dark:bg-teal-950/50 dark:text-teal-400">
                                <flux:icon.user class="size-5" />
                            </span>
                            <div>
                                <flux:heading size="lg">{{ __('general.customer_information') }}</flux:heading>
                                <flux:text class="text-sm text-zinc-500">{{ __('general.customer_information_description') }}</flux:text>
                            </div>
                        </div>

                        @if($this->needsProfileCompletion)
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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
                            <div class="grid grid-cols-1 gap-4 rounded-2xl bg-zinc-50 p-4 dark:bg-zinc-800/60 md:grid-cols-3">
                                <div>
                                    <flux:text class="text-xs text-zinc-500">{{ __('general.first_name') }}</flux:text>
                                    <flux:text class="font-bold">{{ auth()->user()->first_name }}</flux:text>
                                </div>
                                <div>
                                    <flux:text class="text-xs text-zinc-500">{{ __('general.last_name') }}</flux:text>
                                    <flux:text class="font-bold">{{ auth()->user()->last_name }}</flux:text>
                                </div>
                                <div>
                                    <flux:text class="text-xs text-zinc-500">{{ __('general.national_code') }}</flux:text>
                                    <flux:text class="font-bold" dir="ltr">{{ auth()->user()->national_code }}</flux:text>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Shipping Address Section --}}
                    <div class="rounded-3xl border border-zinc-200/80 bg-white p-4 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900 sm:p-6">
                        <div class="mb-6 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="flex size-10 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                                    <flux:icon.map-pin class="size-5" />
                                </span>
                                <flux:heading size="lg">{{ __('general.shipping_address') }}</flux:heading>
                            </div>
                            <flux:button wire:click="openNewAddressForm" variant="primary" color="teal" size="sm" icon="plus">
                                {{ __('general.add_new_address') }}
                            </flux:button>
                        </div>

                        @if($this->addresses->count() > 0)
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                @foreach($this->addresses as $addr)
                                    <div
                                        wire:key="address-{{ $addr->id }}"
                                        class="relative rounded-2xl border p-4 transition-all {{ $selectedAddressId == $addr->id ? 'border-teal-500 bg-teal-50/50 ring-2 ring-teal-500/30 dark:border-teal-500 dark:bg-teal-950/20' : 'border-zinc-200 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:border-zinc-600 dark:hover:bg-zinc-800/60' }}"
                                    >
                                        <label class="flex cursor-pointer items-start gap-3">
                                            <input type="radio" wire:model.live="selectedAddressId" value="{{ $addr->id }}" class="mt-1 accent-teal-600" />
                                            <div class="min-w-0 flex-1">
                                                <div class="mb-1 flex flex-wrap items-center gap-2 font-bold text-zinc-900 dark:text-zinc-100">
                                                    <span class="truncate">{{ $addr->name ?: __('general.address') }}</span>
                                                    @if($addr->is_default)
                                                        <flux:badge size="sm" color="teal" inset="top">{{ __('general.default') }}</flux:badge>
                                                    @endif
                                                </div>
                                                <div class="space-y-0.5 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                                                    <div>{{ $addr->province_name }}، {{ $addr->city_name }}</div>
                                                    <div>{{ $addr->address }}</div>
                                                    @if($addr->postal_code)
                                                        <div>{{ __('general.postal_code') }}: <span dir="ltr">{{ $addr->postal_code }}</span></div>
                                                    @endif
                                                    @if($addr->emergency_contact)
                                                        <div>{{ __('general.emergency_contact') }}: <span dir="ltr">{{ $addr->emergency_contact }}</span></div>
                                                    @endif
                                                </div>
                                            </div>
                                        </label>
                                        <div class="absolute end-3 top-3">
                                            <flux:tooltip content="{{ __('general.delete') }}">
                                                <flux:button
                                                    size="xs"
                                                    variant="ghost"
                                                    color="red"
                                                    icon="trash"
                                                    square
                                                    wire:click="deleteAddress({{ $addr->id }})"
                                                    wire:confirm="{{ __('general.confirm_delete_address') }}"
                                                />
                                            </flux:tooltip>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-200 py-10 text-center dark:border-zinc-700">
                                <flux:icon.map-pin class="mb-3 size-8 text-zinc-300 dark:text-zinc-600" />
                                <flux:text class="mb-4 text-sm text-zinc-500">{{ __('general.select_shipping_address') }}</flux:text>
                                <flux:button wire:click="openNewAddressForm" variant="primary" color="teal" icon="plus">
                                    {{ __('general.add_new_address') }}
                                </flux:button>
                            </div>
                        @endif
                    </div>

                    {{-- New Address Flyout Modal --}}
                    <flux:modal name="order.shipping.new-address" flyout position="right" wire:model="showNewAddressForm" class="w-full md:max-w-xl">
                        <form wire:submit="saveNewAddress" class="flex h-full flex-col gap-5">
                            <flux:heading size="lg">{{ __('general.add_new_address') }}</flux:heading>

                            <flux:field>
                                <flux:label>{{ __('general.address_name') }} <flux:badge size="sm" inset="top">{{ __('general.optional') }}</flux:badge></flux:label>
                                <flux:input wire:model="name" />
                                <flux:error name="name" />
                            </flux:field>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <flux:field>
                                    <flux:label>{{ __('general.province') }}</flux:label>
                                    <flux:select wire:model.live="province_id" searchable placeholder="{{ __('general.select_province') }}">
                                        @foreach($this->provinces as $id => $province)
                                            <flux:select.option value="{{ $id }}">{{ $province }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="province_id" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>{{ __('general.city') }}</flux:label>
                                    <flux:select wire:model="city_id" searchable placeholder="{{ __('general.select_city') }}">
                                        @foreach($this->cities as $cityKey => $cityName)
                                            <flux:select.option value="{{ $cityKey }}">{{ $cityName }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="city_id" />
                                </flux:field>
                            </div>

                            <flux:field>
                                <flux:label>{{ __('general.address') }}</flux:label>
                                <flux:textarea wire:model="address" rows="3" />
                                <flux:error name="address" />
                            </flux:field>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <flux:field>
                                    <flux:label>{{ __('general.postal_code') }}</flux:label>
                                    <flux:input wire:model="postal_code" dir="ltr" />
                                    <flux:error name="postal_code" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>{{ __('general.emergency_contact') }} <flux:badge size="sm" inset="top">{{ __('general.optional') }}</flux:badge></flux:label>
                                    <flux:input wire:model="emergency_contact" dir="ltr" />
                                    <flux:error name="emergency_contact" />
                                </flux:field>
                            </div>

                            <flux:field>
                                <flux:checkbox wire:model="is_default" />
                                <flux:label>{{ __('general.set_as_default_address') }}</flux:label>
                            </flux:field>

                            <div class="mt-auto">
                                <flux:button type="submit" variant="primary" color="orange" class="w-full" wire:loading.attr="disabled" wire:target="saveNewAddress">
                                    {{ __('general.save') }}
                                </flux:button>
                            </div>
                        </form>
                    </flux:modal>

                    {{-- Shipping Methods Section --}}
                    @if($selectedAddressId)
                        @if(count($this->availableShippingMethods) > 0)
                            <div class="rounded-3xl border border-zinc-200/80 bg-white p-4 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900 sm:p-6">
                                <div class="mb-6 flex items-center gap-3">
                                    <span class="flex size-10 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                                        <flux:icon.truck class="size-5" />
                                    </span>
                                    <flux:heading size="lg">{{ __('general.shipping_method') }}</flux:heading>
                                </div>

                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                    @foreach($this->availableShippingMethods as $method)
                                        <label
                                            wire:key="shipping-rate-{{ $method['id'] }}"
                                            class="flex cursor-pointer items-start gap-3 rounded-2xl border p-4 transition-all {{ $selectedShippingRateId == $method['id'] ? 'border-teal-500 bg-teal-50/50 ring-2 ring-teal-500/30 dark:border-teal-500 dark:bg-teal-950/20' : 'border-zinc-200 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:border-zinc-600 dark:hover:bg-zinc-800/60' }}"
                                        >
                                            <input type="radio" wire:model.live="selectedShippingRateId" value="{{ $method['id'] }}" class="mt-1 accent-teal-600" />
                                            <div class="min-w-0 flex-1">
                                                <div class="mb-1 font-bold text-zinc-900 dark:text-zinc-100">
                                                    {{ $method['method_name'] }}
                                                </div>
                                                <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                                    <span class="font-bold text-teal-600 dark:text-teal-400">{{ number_format($method['cost'], 0) }} {{ __('general.toman') }}</span>
                                                    @if($method['estimated_days'])
                                                        <span class="text-zinc-400"> • </span>
                                                        {{ __('general.estimated_delivery') }}: {{ $method['estimated_days'] }}
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
                        @else
                            <flux:callout variant="warning" icon="triangle-alert" heading="{{ __('general.shipping_method') }}">
                                <flux:text>{{ __('general.no_shipping_methods_available') }}</flux:text>
                            </flux:callout>
                        @endif
                    @endif

                    {{-- Customer Note --}}
                    <div class="rounded-3xl border border-zinc-200/80 bg-white p-4 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900 sm:p-6">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex size-10 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                <flux:icon.pencil-square class="size-5" />
                            </span>
                            <flux:heading size="lg">{{ __('general.customer_note') }}</flux:heading>
                        </div>
                        <flux:field>
                            <flux:textarea wire:model="customerNote" rows="3" placeholder="{{ __('general.customer_note_placeholder') }}" />
                        </flux:field>
                    </div>
                </div>

                {{-- Order Summary Sidebar --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-4 rounded-3xl border border-zinc-200/80 bg-white p-4 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900 sm:p-6">
                        <flux:heading size="lg" class="mb-6">{{ __('general.order_summary') }}</flux:heading>

                        <div class="mb-6 space-y-4">
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
                                    <flux:text class="text-sm text-zinc-400">
                                        {{ __('general.select_shipping_method') }}
                                    </flux:text>
                                </div>
                            @endif

                            <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
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
                            color="teal"
                            icon="check-circle"
                            class="w-full"
                            wire:loading.attr="disabled"
                            wire:target="createOrder"
                            :disabled="!$this->canCompleteOrder"
                        >
                            {{ __('general.complete_order') }}
                        </flux:button>

                        @if(count($this->completeOrderBlockers) > 0)
                            <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-amber-700 dark:text-amber-300">
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
