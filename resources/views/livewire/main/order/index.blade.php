<x-slot name="title">
    {{ __('general.my_orders') }}
</x-slot>

<div>
    @auth
        <section class="py-6 antialiased md:py-10">
            <div class="mx-auto max-w-7xl px-4 2xl:px-0">
                {{-- Breadcrumb --}}
                <flux:breadcrumbs class="mb-6">
                    <flux:breadcrumbs.item href="{{ route('home') }}" wire:navigate>{{ __('general.home') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>{{ __('general.my_orders') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>

                {{-- Page Header --}}
                <div class="mb-8 flex items-center gap-3">
                    <span class="flex size-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                        <flux:icon.boxes class="size-6" />
                    </span>
                    <div>
                        <flux:heading size="xl">{{ __('general.my_orders') }}</flux:heading>
                        <flux:text class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('general.my_orders_description') }}</flux:text>
                    </div>
                </div>

                @if($this->orders && $this->orders->count() > 0)
                    <div class="space-y-4">
                        @foreach($this->orders as $order)
                            <div
                                wire:key="order-{{ $order->id }}"
                                class="overflow-hidden rounded-3xl border border-zinc-200/80 bg-white shadow-sm transition-shadow hover:shadow-md dark:border-zinc-800/80 dark:bg-zinc-900"
                            >
                                {{-- Order Header --}}
                                <div class="border-b border-zinc-200/80 p-4 dark:border-zinc-800 sm:p-6">
                                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                        <div>
                                            <div class="mb-2 flex flex-wrap items-center gap-3">
                                                <flux:heading size="lg">
                                                    {{ __('general.order') }} #{{ $order->order_number }}
                                                </flux:heading>
                                                <flux:badge :color="$order->status === 'completed' ? 'emerald' : ($order->status === 'cancelled' ? 'red' : 'amber')">
                                                    {{ __('general.order_status_' . $order->status) }}
                                                </flux:badge>
                                            </div>
                                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ __('general.order_date') }}: {{ jalali($order->created_at) }}
                                            </flux:text>
                                        </div>
                                        <div class="flex items-center gap-4 md:flex-col md:items-end md:gap-2">
                                            <flux:heading size="md" class="text-zinc-900 dark:text-zinc-100">
                                                {{ number_format($order->total_amount, 0) }} {{ __('general.toman') }}
                                            </flux:heading>
                                            <flux:button
                                                href="{{ route('order.view', ['id' => $order->id]) }}"
                                                variant="primary"
                                                color="teal"
                                                size="sm"
                                                icon:trailing="chevron-left"
                                                wire:navigate
                                            >
                                                {{ __('general.view_order') }}
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Order Items --}}
                                <div class="p-4 sm:p-6">
                                    <div class="space-y-3">
                                        @foreach($order->items->take(3) as $item)
                                            <div class="flex items-center gap-4">
                                                <div class="min-w-0 flex-1">
                                                    <flux:text class="truncate font-medium text-zinc-900 dark:text-zinc-100">
                                                        {{ $item->name }}
                                                    </flux:text>
                                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                                        {{ __('general.quantity') }}: {{ $item->quantity }} × {{ number_format($item->unit_price_amount, 0) }} {{ __('general.toman') }}
                                                    </flux:text>
                                                </div>
                                                <flux:text class="shrink-0 font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ number_format($item->total_amount, 0) }} {{ __('general.toman') }}
                                                </flux:text>
                                            </div>
                                        @endforeach
                                        @if($order->items->count() > 3)
                                            <flux:text class="text-sm italic text-zinc-600 dark:text-zinc-400">
                                                {{ __('general.and_more_items', ['count' => $order->items->count() - 3]) }}
                                            </flux:text>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-3xl border border-zinc-200/80 bg-white p-12 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900">
                        <div class="text-center">
                            <span class="mx-auto mb-6 flex size-20 items-center justify-center rounded-3xl bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500">
                                <flux:icon.boxes class="size-10" />
                            </span>
                            <flux:heading size="xl" class="mb-4">{{ __('general.no_orders_found') }}</flux:heading>
                            <flux:text class="mb-8 text-zinc-600 dark:text-zinc-400">
                                {{ __('general.no_orders_found_description') }}
                            </flux:text>
                            <flux:button href="{{ route('home') }}" variant="primary" color="teal" wire:navigate>
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
                <div class="rounded-3xl border border-zinc-200/80 bg-white p-12 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900">
                    <div class="text-center">
                        <flux:heading size="xl" class="mb-4">{{ __('general.please_login') }}</flux:heading>
                        <flux:text class="mb-8 text-zinc-600 dark:text-zinc-400">
                            {{ __('general.please_login_to_view_orders') }}
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
