<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.orders') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.orders_description') }}</flux:subheading>
            </div>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <div class="mb-6">
        <flux:field>
            <flux:label>{{ __('general.search') }}</flux:label>
            <flux:input wire:model.live.debounce.500ms="search" type="text" placeholder="{{ __('general.search') }}" />
        </flux:field>
    </div>

    <livewire:panel.shop.order.view />
    <livewire:panel.shop.order.ship />

    <flux:table :paginate="$this->orders">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'order_number'" :direction="$sortDirection" wire:click="sort('order_number')">{{ __('app.order_number') }}</flux:table.column>
            <flux:table.column>{{ __('app.customer') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'total_amount'" :direction="$sortDirection" wire:click="sort('total_amount')">{{ __('app.total_amount') }}</flux:table.column>
            <flux:table.column>{{ __('app.status') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('app.order_date') }}</flux:table.column>
            <flux:table.column>{{ __('general.options') }}</flux:table.column>
        </flux:table.columns>

        @foreach ($this->orders as $order)
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
                    @php
                        $statusColor = match($order->status) {
                            'pending' => 'orange',
                            'processing' => 'sky',
                            'shipped' => 'purple',
                            'delivered' => 'green',
                            'cancelled' => 'danger',
                            default => 'slate',
                        };
                    @endphp
                    <flux:badge color="{{ $statusColor }}">{{ __('app.order_status_' . $order->status) }}</flux:badge>
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ jalali($order->created_at) }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <flux:tooltip content="{{ __('app.view_order') }}">
                            <flux:button size="xs" variant="primary" icon="eye" icon:variant="outline" wire:click="$dispatch('panel.shop.order.view.assign-data', { id: '{{ $order->id }}' })" />
                        </flux:tooltip>
                        @if ($order->status === 'processing' && $order->paid_at)
                            <flux:tooltip content="{{ __('app.ship_order') }}">
                                <flux:button size="xs" variant="primary" color="green" icon="truck" icon:variant="outline" wire:click="$dispatch('panel.shop.order.ship.assign-data', { id: '{{ $order->id }}' })" />
                            </flux:tooltip>
                        @endif
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
