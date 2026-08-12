<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.my_orders') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.my_orders_description') }}</flux:subheading>
            </div>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <flux:card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <flux:field>
                <flux:label>{{ __('app.search') }}</flux:label>
                <flux:input wire:model.live.debounce.500ms="search" type="text" placeholder="{{ __('app.order_number') }}" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('app.status') }}</flux:label>
                <flux:select wire:model.live="status" searchable>
                    <flux:select.option value="">{{ __('app.all_statuses') }}</flux:select.option>
                    <flux:select.option value="pending">{{ __('app.order_status_pending') }}</flux:select.option>
                    <flux:select.option value="processing">{{ __('app.order_status_processing') }}</flux:select.option>
                    <flux:select.option value="shipped">{{ __('app.order_status_shipped') }}</flux:select.option>
                    <flux:select.option value="delivered">{{ __('app.order_status_delivered') }}</flux:select.option>
                    <flux:select.option value="cancelled">{{ __('app.order_status_cancelled') }}</flux:select.option>
                    <flux:select.option value="completed">{{ __('app.order_status_completed') }}</flux:select.option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('app.payment_status') }}</flux:label>
                <flux:select wire:model.live="paymentStatus" searchable>
                    <flux:select.option value="">{{ __('app.all_statuses') }}</flux:select.option>
                    <flux:select.option value="paid">{{ __('app.paid') }}</flux:select.option>
                    <flux:select.option value="unpaid">{{ __('app.unpaid') }}</flux:select.option>
                </flux:select>
            </flux:field>
        </div>
    </flux:card>

    <livewire:panel.user.order.view />

    <flux:table :paginate="$this->orders">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'order_number'" :direction="$sortDirection" wire:click="sort('order_number')">{{ __('app.order_number') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('app.order_date') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'total_amount'" :direction="$sortDirection" wire:click="sort('total_amount')">{{ __('app.total_amount') }}</flux:table.column>
            <flux:table.column>{{ __('app.status') }}</flux:table.column>
            <flux:table.column>{{ __('app.payment_status') }}</flux:table.column>
            <flux:table.column>{{ __('app.options') }}</flux:table.column>
        </flux:table.columns>

        @foreach ($this->orders as $order)
            <flux:table.row :key="$order->id">
                <flux:table.cell class="whitespace-nowrap">
                    {{ $order->order_number }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ jalali($order->created_at) }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ number_format((float) $order->total_amount) }} {{ $order->currency ?? __('app.rial') }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    @php
                        $statusColor = match($order->status) {
                            'pending' => 'orange',
                            'processing' => 'sky',
                            'shipped' => 'purple',
                            'delivered', 'completed' => 'green',
                            'cancelled' => 'red',
                            default => 'zinc',
                        };
                    @endphp
                    <flux:badge color="{{ $statusColor }}">{{ __('app.order_status_' . $order->status) }}</flux:badge>
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    @if ($order->paid_at)
                        <flux:badge color="green">{{ __('app.paid') }}</flux:badge>
                    @else
                        <flux:badge color="orange">{{ __('app.unpaid') }}</flux:badge>
                    @endif
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <flux:tooltip content="{{ __('app.view_order') }}">
                            <flux:button
                                size="xs"
                                variant="primary"
                                color="sky"
                                icon="eye"
                                icon:variant="outline"
                                wire:click="$dispatch('panel.user.order.view.assign-data', { id: {{ $order->id }} })"
                            />
                        </flux:tooltip>

                        @can('user_order_pay')
                            @if (! $order->paid_at && ! $order->cancelled_at)
                                <flux:tooltip content="{{ __('app.pay_now') }}">
                                    <flux:button
                                        size="xs"
                                        variant="primary"
                                        color="green"
                                        icon="credit-card"
                                        icon:variant="outline"
                                        href="{{ route('order.payment', ['id' => $order->id]) }}"
                                    />
                                </flux:tooltip>
                            @endif
                        @endcan
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
