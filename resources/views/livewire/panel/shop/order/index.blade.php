<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.orders') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.orders_description') }}</flux:subheading>
            </div>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <flux:card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <flux:field>
                <flux:label>{{ __('general.search') }}</flux:label>
                <flux:input wire:model.live.debounce.300ms="search" clearable type="text" placeholder="{{ __('general.order_number') }}" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('general.status') }}</flux:label>
                <flux:select wire:model.live="status" searchable>
                    <flux:select.option value="">{{ __('general.all_statuses') }}</flux:select.option>
                    @foreach (\App\Enums\OrderStatusEnum::cases() as $status)
                        <flux:select.option value="{{ $status->value }}">{{ $status->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('general.payment_status') }}</flux:label>
                <flux:select wire:model.live="paymentStatus" searchable>
                    <flux:select.option value="">{{ __('general.all_statuses') }}</flux:select.option>
                    <flux:select.option value="paid">{{ __('general.paid') }}</flux:select.option>
                    <flux:select.option value="unpaid">{{ __('general.pending_payment') }}</flux:select.option>
                </flux:select>
            </flux:field>
        </div>
    </flux:card>

    <livewire:panel.shop.order.view />
    <livewire:panel.shop.order.ship />

    <flux:table :paginate="$this->orders">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'order_number'" :direction="$sortDirection" wire:click="sort('order_number')">{{ __('general.order_number') }}</flux:table.column>
            <flux:table.column>{{ __('general.customer') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'total_amount'" :direction="$sortDirection" wire:click="sort('total_amount')">{{ __('general.total_amount') }}</flux:table.column>
            <flux:table.column>{{ __('general.status') }}</flux:table.column>
            <flux:table.column>{{ __('general.payment_status') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.order_date') }}</flux:table.column>
            <flux:table.column>{{ __('general.options') }}</flux:table.column>
        </flux:table.columns>

        @forelse ($this->orders as $order)
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
                    @php($orderStatus = \App\Enums\OrderStatusEnum::tryFromSafe($order->status))
                    <flux:badge color="{{ $orderStatus->color() }}">{{ $orderStatus->label() }}</flux:badge>
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    @if ($order->paid_at)
                        <flux:badge color="green">{{ __('general.paid') }}</flux:badge>
                    @elseif ($order->cancelled_at)
                        <flux:badge color="red">{{ __('general.order_status_cancelled') }}</flux:badge>
                    @else
                        <flux:badge color="orange">{{ __('general.pending_payment') }}</flux:badge>
                    @endif
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ jalali($order->created_at) }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <flux:tooltip content="{{ __('general.view_order') }}">
                            <flux:button size="xs" variant="primary" icon="eye" icon:variant="outline" wire:click="$dispatch('panel.shop.order.view.assign-data', { id: '{{ $order->id }}' })" />
                        </flux:tooltip>
                        @if ($order->status === 'processing' && $order->paid_at)
                            <flux:tooltip content="{{ __('general.ship_order') }}">
                                <flux:button size="xs" variant="primary" color="green" icon="truck" icon:variant="outline" wire:click="$dispatch('panel.shop.order.ship.assign-data', { id: '{{ $order->id }}' })" />
                            </flux:tooltip>
                        @endif
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @empty
            <flux:table.row>
                <flux:table.cell colspan="7">{{ __('general.no_results') }}</flux:table.cell>
            </flux:table.row>
        @endforelse
    </flux:table>
</div>
