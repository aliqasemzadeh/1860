<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.shop_dashboard') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.shop_dashboard_description') }}</flux:subheading>
            </div>
            <flux:tooltip content="{{ __('general.refresh') }}">
                <flux:button
                    size="sm"
                    variant="primary"
                    color="teal"
                    icon="refresh-cw"
                    icon:variant="outline"
                    wire:click="refresh"
                />
            </flux:tooltip>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:panel.shop.order.view />

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <a href="{{ route('panel.shop.product.index') }}" wire:navigate class="block">
            <flux:card class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                <flux:text class="text-sm text-zinc-500">{{ __('general.products_count') }}</flux:text>
                <flux:heading size="xl">{{ $this->stats['products_count'] }}</flux:heading>
            </flux:card>
        </a>
        <a href="{{ route('panel.shop.product.index') }}" wire:navigate class="block">
            <flux:card class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                <flux:text class="text-sm text-zinc-500">{{ __('general.out_of_stock_products') }}</flux:text>
                <flux:heading size="xl" class="text-orange-600">{{ $this->stats['out_of_stock_count'] }}</flux:heading>
            </flux:card>
        </a>
        <a href="{{ route('panel.shop.order.index') }}" wire:navigate class="block">
            <flux:card class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                <flux:text class="text-sm text-zinc-500">{{ __('general.orders_count') }}</flux:text>
                <flux:heading size="xl">{{ $this->stats['orders_count'] }}</flux:heading>
            </flux:card>
        </a>
        <a href="{{ route('panel.shop.order.index') }}" wire:navigate class="block">
            <flux:card class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                <flux:text class="text-sm text-zinc-500">{{ __('general.paid_total') }}</flux:text>
                <flux:heading size="xl" class="text-green-600">
                    {{ number_format($this->stats['paid_total']) }} {{ __('general.toman') }}
                </flux:heading>
            </flux:card>
        </a>
    </div>

    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">{{ __('general.orders_by_status') }}</flux:heading>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @foreach (\App\Enums\OrderStatusEnum::cases() as $status)
                <a href="{{ route('panel.shop.order.index') }}" wire:navigate class="block space-y-2">
                    <flux:badge color="{{ $status->color() }}">{{ $status->label() }}</flux:badge>
                    <flux:heading size="lg">{{ $this->stats['status_counts'][$status->value] ?? 0 }}</flux:heading>
                </a>
            @endforeach
        </div>
    </flux:card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <flux:card class="lg:col-span-2 space-y-4">
            <flux:heading size="lg">{{ __('general.recent_orders') }}</flux:heading>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('general.order_number') }}</flux:table.column>
                    <flux:table.column>{{ __('general.customer') }}</flux:table.column>
                    <flux:table.column>{{ __('general.total_amount') }}</flux:table.column>
                    <flux:table.column>{{ __('general.status') }}</flux:table.column>
                    <flux:table.column>{{ __('general.order_date') }}</flux:table.column>
                    <flux:table.column>{{ __('general.options') }}</flux:table.column>
                </flux:table.columns>

                @forelse ($this->recentOrders as $order)
                    @php
                        $orderStatus = \App\Enums\OrderStatusEnum::tryFromSafe($order->status);
                    @endphp
                    <flux:table.row :key="$order->id">
                        <flux:table.cell class="whitespace-nowrap">
                            {{ $order->order_number }}
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            {{ $order->user?->name ?? '-' }}
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            {{ number_format((float) $order->total_amount) }} {{ $order->currency }}
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            <flux:badge color="{{ $orderStatus->color() }}">{{ $orderStatus->label() }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            {{ jalali($order->created_at) }}
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            @can('shop_order_view')
                                <flux:tooltip content="{{ __('general.view_order') }}">
                                    <flux:button
                                        size="xs"
                                        variant="primary"
                                        icon="eye"
                                        icon:variant="outline"
                                        wire:click="$dispatch('panel.shop.order.view.assign-data', { id: '{{ $order->id }}' })"
                                    />
                                </flux:tooltip>
                            @endcan
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6">{{ __('general.no_results') }}</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table>
        </flux:card>

        <div class="space-y-4">
            <flux:card class="space-y-4">
                <div>
                    <flux:text class="text-sm text-zinc-500">{{ __('general.today_orders_count') }}</flux:text>
                    <flux:heading size="xl">{{ $this->stats['today_count'] }}</flux:heading>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500">{{ __('general.today_paid_total') }}</flux:text>
                    <flux:heading size="xl" class="text-green-600">
                        {{ number_format($this->stats['today_paid_total']) }} {{ __('general.toman') }}
                    </flux:heading>
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('general.catalog') }}</flux:heading>
                <a href="{{ route('panel.shop.setting-management.category.index') }}" wire:navigate class="block">
                    <flux:text class="text-sm text-zinc-500">{{ __('general.categories_count') }}</flux:text>
                    <flux:heading size="xl">{{ $this->stats['categories_count'] }}</flux:heading>
                </a>
                <a href="{{ route('panel.shop.setting-management.brand.index') }}" wire:navigate class="block">
                    <flux:text class="text-sm text-zinc-500">{{ __('general.brands_count') }}</flux:text>
                    <flux:heading size="xl">{{ $this->stats['brands_count'] }}</flux:heading>
                </a>
            </flux:card>
        </div>
    </div>
</div>
