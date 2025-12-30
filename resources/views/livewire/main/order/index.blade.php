<x-slot name="title">
    {{ __('app.my_orders') }}
</x-slot>

<div>
    @auth
        <section class="py-8 antialiased md:py-12">
            <div class="mx-auto max-w-7xl px-4 2xl:px-0">
                {{-- Breadcrumb --}}
                <nav class="mb-6 flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                    <a href="{{ route('home') }}" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                        {{ __('app.dashboard') }}
                    </a>
                    <span>/</span>
                    <span class="text-zinc-900 dark:text-zinc-100 font-medium">{{ __('app.my_orders') }}</span>
                </nav>

                {{-- Page Header --}}
                <div class="mb-8">
                    <flux:heading size="xl">{{ __('app.my_orders') }}</flux:heading>
                    <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('app.my_orders_description') }}</flux:text>
                </div>

                @if($this->orders && $this->orders->count() > 0)
                    <div class="space-y-4">
                        @foreach($this->orders as $order)
                            <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
                                {{-- Order Header --}}
                                <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                        <div>
                                            <div class="flex items-center gap-3 mb-2">
                                                <flux:heading size="lg">
                                                    {{ __('app.order') }} #{{ $order->order_number }}
                                                </flux:heading>
                                                <flux:badge variant="{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">
                                                    {{ __('app.order_status_' . $order->status) }}
                                                </flux:badge>
                                            </div>
                                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ __('app.order_date') }}: {{ \Morilog\Jalali\Jalalian::forge($order->created_at)->format('Y/m/d H:i') }}
                                            </flux:text>
                                        </div>
                                        <div class="flex flex-col md:items-end gap-2">
                                            <flux:heading size="md" class="text-zinc-900 dark:text-zinc-100">
                                                {{ number_format($order->total_amount, 0) }} {{ __('app.toman') }}
                                            </flux:heading>
                                            <flux:button 
                                                href="{{ route('order.view', ['id' => $order->id]) }}" 
                                                variant="ghost" 
                                                size="sm"
                                                wire:navigate
                                            >
                                                {{ __('app.view_order') }}
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Order Items --}}
                                <div class="p-6">
                                    <div class="space-y-3">
                                        @foreach($order->items->take(3) as $item)
                                            <div class="flex items-center gap-4">
                                                <div class="flex-1">
                                                    <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                                        {{ $item->name }}
                                                    </flux:text>
                                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                                        {{ __('app.quantity') }}: {{ $item->quantity }} × {{ number_format($item->unit_price_amount, 0) }} {{ __('app.toman') }}
                                                    </flux:text>
                                                </div>
                                                <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ number_format($item->total_amount, 0) }} {{ __('app.toman') }}
                                                </flux:text>
                                            </div>
                                        @endforeach
                                        @if($order->items->count() > 3)
                                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 italic">
                                                {{ __('app.and_more_items', ['count' => $order->items->count() - 3]) }}
                                            </flux:text>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm p-12">
                        <div class="text-center">
                            <flux:icon.boxes class="w-24 h-24 mx-auto text-zinc-400 dark:text-zinc-600 mb-6" />
                            <flux:heading size="xl" class="mb-4">{{ __('app.no_orders_found') }}</flux:heading>
                            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">
                                {{ __('app.no_orders_found_description') }}
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
                            {{ __('app.please_login_to_view_orders') }}
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
