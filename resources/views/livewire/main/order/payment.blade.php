<x-slot name="title">
    {{ __('general.payment') }} - {{ __('general.order') }} #{{ $this->order->order_number ?? '' }}
</x-slot>

<div>
    @if ($paymentHtml)
        <div id="payment-form-container" x-data="{ submitForm() { this.$nextTick(() => { const form = this.$el.querySelector('form'); if (form) { form.submit(); } else { const scripts = this.$el.querySelectorAll('script'); scripts.forEach(script => { const newScript = document.createElement('script'); Array.from(script.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value)); newScript.appendChild(document.createTextNode(script.innerHTML)); script.parentNode.replaceChild(newScript, script); }); } }); } }" x-init="submitForm()">
            <div class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white dark:bg-zinc-950">
                <flux:heading size="xl" class="mb-4">{{ __('general.redirecting_to_gateway') }}</flux:heading>
                <div class="h-12 w-12 animate-spin rounded-full border-b-2 border-zinc-900 dark:border-white"></div>
            </div>
            {!! $paymentHtml !!}
        </div>
    @endif

    @auth
        @if($this->order)
            <section class="py-6 antialiased md:py-10">
                <div class="mx-auto max-w-7xl px-4 2xl:px-0">
                    {{-- Breadcrumb --}}
                    <flux:breadcrumbs class="mb-6">
                        <flux:breadcrumbs.item href="{{ route('home') }}" wire:navigate>{{ __('general.home') }}</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item href="{{ route('order.index') }}" wire:navigate>{{ __('general.my_orders') }}</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item href="{{ route('order.view', ['id' => $this->order->id]) }}" wire:navigate>{{ __('general.order') }} #{{ $this->order->order_number }}</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item>{{ __('general.payment') }}</flux:breadcrumbs.item>
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
                            <span class="flex size-8 items-center justify-center rounded-full bg-teal-600 text-white shadow-sm shadow-teal-500/30">
                                <flux:icon.check class="size-4" />
                            </span>
                            <span class="hidden text-xs font-bold text-teal-600 sm:inline dark:text-teal-400">{{ __('general.shipping') }}</span>
                        </div>
                        <span class="h-0.5 w-8 rounded-full bg-teal-500 sm:w-16"></span>
                        <div class="flex items-center gap-2">
                            <span class="flex size-8 items-center justify-center rounded-full bg-teal-600 text-white shadow-sm shadow-teal-500/30 ring-4 ring-teal-100 dark:ring-teal-950">
                                <flux:icon.credit-card class="size-4" />
                            </span>
                            <span class="text-xs font-black text-zinc-900 sm:text-sm dark:text-zinc-50">{{ __('general.payment') }}</span>
                        </div>
                    </div>

                    {{-- Payment Info --}}
                    <div class="mx-auto max-w-2xl">
                        <div class="rounded-3xl border border-zinc-200/80 bg-white p-5 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900 sm:p-8">
                            <div class="mb-8 text-center">
                                <span class="mx-auto mb-4 flex size-16 items-center justify-center rounded-3xl bg-teal-50 text-teal-600 dark:bg-teal-950/50 dark:text-teal-400">
                                    <flux:icon.credit-card class="size-8" />
                                </span>
                                <flux:heading size="xl" class="mb-2">{{ __('general.payment') }}</flux:heading>
                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                    {{ __('general.payment_description') }}
                                </flux:text>
                            </div>

                            {{-- Order Summary --}}
                            <div class="mb-8 space-y-3 rounded-2xl bg-zinc-50 p-5 dark:bg-zinc-800/60">
                                <div class="flex items-center justify-between">
                                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('general.order_number') }}</flux:text>
                                    <flux:text class="font-bold text-zinc-900 dark:text-zinc-100">
                                        {{ $this->order->order_number }}
                                    </flux:text>
                                </div>

                                @if($this->order->items->isNotEmpty())
                                    <div class="space-y-2 border-t border-zinc-200 pt-3 dark:border-zinc-700">
                                        @foreach($this->order->items as $item)
                                            <div class="flex items-center justify-between gap-3 text-sm">
                                                <flux:text class="min-w-0 truncate text-zinc-600 dark:text-zinc-400">
                                                    {{ $item->name }} <span class="text-zinc-400">× {{ $item->quantity }}</span>
                                                </flux:text>
                                                <flux:text class="shrink-0 text-zinc-900 dark:text-zinc-100">
                                                    {{ number_format($item->total_amount, 0) }} {{ __('general.toman') }}
                                                </flux:text>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if($this->order->shipping_amount > 0)
                                    <div class="flex items-center justify-between border-t border-zinc-200 pt-3 text-sm dark:border-zinc-700">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            {{ __('general.shipping') }}
                                            @if($this->order->shippingMethod)
                                                ({{ $this->order->shippingMethod->name }})
                                            @endif
                                        </flux:text>
                                        <flux:text class="text-zinc-900 dark:text-zinc-100">
                                            {{ number_format($this->order->shipping_amount, 0) }} {{ __('general.toman') }}
                                        </flux:text>
                                    </div>
                                @endif

                                <div class="flex items-center justify-between border-t border-zinc-200 pt-3 dark:border-zinc-700">
                                    <flux:heading size="md" class="text-zinc-900 dark:text-zinc-100">{{ __('general.total') }}</flux:heading>
                                    <flux:heading size="lg" class="text-teal-600 dark:text-teal-400">
                                        {{ number_format($this->order->total_amount, 0) }} {{ __('general.toman') }}
                                    </flux:heading>
                                </div>
                            </div>

                            {{-- Payment Button --}}
                            <div class="space-y-3">
                                <flux:button
                                    wire:click="pay"
                                    variant="primary"
                                    color="teal"
                                    icon="credit-card"
                                    class="w-full"
                                    wire:loading.attr="disabled"
                                    wire:target="pay"
                                >
                                    <span wire:loading.remove wire:target="pay">{{ __('general.pay_now') }}</span>
                                    <span wire:loading wire:target="pay">...</span>
                                </flux:button>

                                <flux:button
                                    href="{{ route('order.view', ['id' => $this->order->id]) }}"
                                    variant="ghost"
                                    class="w-full"
                                    wire:navigate
                                >
                                    {{ __('general.cancel') }}
                                </flux:button>
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
