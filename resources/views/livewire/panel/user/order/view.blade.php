<div>
    <flux:modal name="panel.user.order.view.modal" class="md:w-[600px]" flyout position="right">
        @if ($order)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('app.order_details') }} #{{ $order->order_number }}</flux:heading>
                    <flux:subheading>
                        {{ __('app.order_date') }}:
                        {{ \Morilog\Jalali\Jalalian::fromCarbon($order->created_at)->format('%Y/%m/%d %H:%M') }}
                    </flux:subheading>
                </div>

                <flux:separator />

                <div class="space-y-2">
                    <flux:heading size="sm">{{ __('app.shipping_address') }}</flux:heading>
                    @if ($order->shipping_address)
                        <flux:text>{{ $order->shipping_address['address'] ?? '-' }}</flux:text>
                        <flux:text>{{ $order->shipping_address['city'] ?? '-' }}, {{ $order->shipping_address['province'] ?? '-' }}</flux:text>
                        <flux:text>{{ __('app.postal_code') }}: {{ $order->shipping_address['postal_code'] ?? '-' }}</flux:text>
                    @else
                        <flux:text>-</flux:text>
                    @endif
                </div>

                <flux:separator />

                <div class="space-y-4">
                    <flux:heading size="sm">{{ __('app.order_items') }}</flux:heading>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('app.name') }}</flux:table.column>
                            <flux:table.column>{{ __('app.quantity') }}</flux:table.column>
                            <flux:table.column>{{ __('app.total_amount') }}</flux:table.column>
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
                        <flux:text>{{ __('app.subtotal') }}:</flux:text>
                        <flux:text>{{ number_format((float) $order->subtotal_amount) }} {{ $order->currency ?? __('app.rial') }}</flux:text>
                    </div>
                    <div class="flex justify-between">
                        <flux:text>{{ __('app.shipping_cost') }}:</flux:text>
                        <flux:text>{{ number_format((float) $order->shipping_amount) }} {{ $order->currency ?? __('app.rial') }}</flux:text>
                    </div>
                    <div class="flex justify-between font-bold">
                        <flux:heading size="sm">{{ __('app.total_amount') }}:</flux:heading>
                        <flux:heading size="sm">{{ number_format((float) $order->total_amount) }} {{ $order->currency ?? __('app.rial') }}</flux:heading>
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
                            {{ __('app.pay_now') }}
                        </flux:button>
                    @endif
                @endcan
            </div>
        @endif
    </flux:modal>
</div>
