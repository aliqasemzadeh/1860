<x-slot name="title">
    {{ __('app.payment') }} - {{ __('app.order') }} #{{ $this->order->order_number ?? '' }}
</x-slot>

<div>
    @if ($paymentHtml)
        <div id="payment-form-container" x-data="{ submitForm() { this.$nextTick(() => { const form = this.$el.querySelector('form'); if (form) { form.submit(); } else { const scripts = this.$el.querySelectorAll('script'); scripts.forEach(script => { const newScript = document.createElement('script'); Array.from(script.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value)); newScript.appendChild(document.createTextNode(script.innerHTML)); script.parentNode.replaceChild(newScript, script); }); } }); } }" x-init="submitForm()">
            <div class="fixed inset-0 bg-white dark:bg-zinc-950 z-50 flex flex-col items-center justify-center">
                <flux:heading size="xl" class="mb-4">{{ __('app.redirecting_to_gateway') }}</flux:heading>
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-zinc-900 dark:border-white"></div>
            </div>
            {!! $paymentHtml !!}
        </div>
    @endif

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
                        <a href="{{ route('order.view', ['id' => $this->order->id]) }}" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                            {{ __('app.order') }} #{{ $this->order->order_number }}
                        </a>
                        <span>/</span>
                        <span class="text-zinc-900 dark:text-zinc-100 font-medium">{{ __('app.payment') }}</span>
                    </nav>

                    {{-- Payment Info --}}
                    <div class="max-w-2xl mx-auto">
                        <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-8">
                            <div class="text-center mb-8">
                                <flux:heading size="xl" class="mb-4">{{ __('app.payment') }}</flux:heading>
                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                    {{ __('app.payment_description') }}
                                </flux:text>
                            </div>

                            {{-- Order Summary --}}
                            <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-6 mb-8">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('app.order_number') }}</flux:text>
                                        <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $this->order->order_number }}
                                        </flux:text>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('app.total') }}</flux:text>
                                        <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                                            {{ number_format($this->order->total_amount, 0) }} {{ __('app.toman') }}
                                        </flux:heading>
                                    </div>
                                </div>
                            </div>

                            {{-- Payment Button --}}
                            <div class="space-y-4">
                                <flux:button
                                    wire:click="pay"
                                    variant="primary"
                                    class="w-full"
                                    size="base"
                                >
                                    {{ __('app.pay_now') }}
                                </flux:button>

                                <flux:button
                                    href="{{ route('order.view', ['id' => $this->order->id]) }}"
                                    variant="ghost"
                                    class="w-full"
                                    wire:navigate
                                >
                                    {{ __('app.cancel') }}
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
