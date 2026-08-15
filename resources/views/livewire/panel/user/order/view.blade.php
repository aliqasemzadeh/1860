<div>
    <flux:modal name="panel.user.order.view.modal" class="md:w-[600px]" flyout position="right">
        @if ($order)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('general.order_details') }} #{{ $order->order_number }}</flux:heading>
                    <flux:subheading>
                        {{ __('general.order_date') }}:
                        {{ jalali($order->created_at) }}
                    </flux:subheading>
                </div>

                <flux:separator />

                <div class="space-y-2">
                    <flux:heading size="sm">{{ __('general.shipping_address') }}</flux:heading>
                    @if ($order->shipping_address)
                        <flux:text>{{ $order->shipping_address['address'] ?? '-' }}</flux:text>
                        <flux:text>{{ $order->shipping_address['city'] ?? '-' }}, {{ $order->shipping_address['province'] ?? '-' }}</flux:text>
                        <flux:text>{{ __('general.postal_code') }}: {{ $order->shipping_address['postal_code'] ?? '-' }}</flux:text>
                    @else
                        <flux:text>-</flux:text>
                    @endif
                </div>

                <flux:separator />

                @if ($order->tracking_code || $order->shipped_at || $order->delivered_at)
                    <div class="space-y-2">
                        <flux:heading size="sm">{{ __('general.shipping_info') }}</flux:heading>
                        @if ($order->tracking_code)
                            <flux:text><strong>{{ __('general.tracking_code') }}:</strong> {{ $order->tracking_code }}</flux:text>
                        @endif
                        @if ($order->shipped_at)
                            <flux:text><strong>{{ __('general.shipped_at') }}:</strong> {{ jalali($order->shipped_at) }}</flux:text>
                        @endif
                        @if ($order->delivered_at)
                            <flux:text><strong>{{ __('general.delivered_at') }}:</strong> {{ jalali($order->delivered_at) }}</flux:text>
                        @endif
                    </div>

                    <flux:separator />
                @endif

                <div class="space-y-4">
                    <flux:heading size="sm">{{ __('general.order_items') }}</flux:heading>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('general.name') }}</flux:table.column>
                            <flux:table.column>{{ __('general.quantity') }}</flux:table.column>
                            <flux:table.column>{{ __('general.total_amount') }}</flux:table.column>
                        </flux:table.columns>

                        @foreach ($order->items as $item)
                            <flux:table.row :key="$item->id">
                                <flux:table.cell>{{ $item->name }}</flux:table.cell>
                                <flux:table.cell>{{ $item->quantity }}</flux:table.cell>
                                <flux:table.cell>{{ number_format((float) $item->total_amount) }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table>
                </div>

                <flux:separator />

                <div class="space-y-2">
                    <div class="flex justify-between">
                        <flux:text>{{ __('general.subtotal') }}:</flux:text>
                        <flux:text>{{ number_format((float) $order->subtotal_amount) }} {{ $order->currency ?? __('general.rial') }}</flux:text>
                    </div>
                    <div class="flex justify-between">
                        <flux:text>{{ __('general.shipping_cost') }}:</flux:text>
                        <flux:text>{{ number_format((float) $order->shipping_amount) }} {{ $order->currency ?? __('general.rial') }}</flux:text>
                    </div>
                    <div class="flex justify-between font-bold">
                        <flux:heading size="sm">{{ __('general.total_amount') }}:</flux:heading>
                        <flux:heading size="sm">{{ number_format((float) $order->total_amount) }} {{ $order->currency ?? __('general.rial') }}</flux:heading>
                    </div>
                </div>

                @can('user_order_pay')
                    @if (! $order->paid_at && ! $order->cancelled_at)
                        <flux:button
                            class="w-full"
                            variant="primary"
                            color="green"
                            href="{{ route('order.payment', ['id' => $order->id]) }}"
                        >
                            {{ __('general.pay_now') }}
                        </flux:button>
                    @endif
                @endcan
            </div>
        @endif
    </flux:modal>
</div>
