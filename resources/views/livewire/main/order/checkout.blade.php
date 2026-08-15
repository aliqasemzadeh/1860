<x-slot name="title">
    {{ __('general.checkout') }}
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
                <span class="text-zinc-900 dark:text-zinc-100 font-medium">{{ __('general.checkout') }}</span>
            </nav>

            {{-- Page Header --}}
            <div class="mb-8">
                <flux:heading size="xl">{{ __('general.checkout') }}</flux:heading>
                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('general.checkout_description') }}</flux:text>
            </div>

            <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-8 max-w-md mx-auto">
                @if ($step === 1)
                    <form wire:submit="send" class="space-y-6">
                        <div class="space-y-2">
                            <flux:heading size="lg" class="text-center">{{ __('general.login_with_mobile') }}</flux:heading>
                            <flux:text class="text-center text-zinc-600 dark:text-zinc-400">{{ __('general.enter_mobile_prompt') }}</flux:text>
                        </div>
                        <flux:field>
                            <flux:label>{{ __('general.mobile') }}</flux:label>
                            <flux:input dir="ltr" wire:model="mobile" placeholder="09123456789" />
                            <flux:error name="mobile" />
                        </flux:field>
                        <div>
                            <flux:button variant="primary" type="submit" class="w-full">{{ __('general.send_code') }}</flux:button>
                        </div>
                    </form>
                @else
                    <form wire:submit="verify" class="space-y-8">
                        <div class="space-y-2">
                            <flux:heading size="lg" class="text-center">{{ __('general.verify_login') }}</flux:heading>
                            <flux:text class="text-center text-zinc-600 dark:text-zinc-400">{{ $mobile }}</flux:text>
                            <flux:text class="text-center text-zinc-600 dark:text-zinc-400">{{ __('general.mobile_confirm_question') }}</flux:text>
                        </div>
                        <flux:field>
                            <flux:label>{{ __('general.otp_code_label') }}</flux:label>
                            <flux:otp wire:model="code" dir="ltr" length="6" />
                            <flux:error name="code" />
                        </flux:field>
                        <div class="space-y-4">
                            <flux:button variant="primary" type="submit" class="w-full">{{ __('general.verify') }}</flux:button>
                            <flux:button type="button" wire:click="resend" variant="ghost" class="w-full">{{ __('general.resend_code') }}</flux:button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </section>
</div>
