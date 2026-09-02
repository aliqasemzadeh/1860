<div>
    <flux:modal name="panel.shop.order.view.modal" class="md:w-[800px]" flyout position="right">
        @if ($order)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('general.order_details') }} #{{ $order->order_number }}</flux:heading>
                    <flux:subheading>{{ __('general.order_date') }}: {{ jalali($order->created_at) }}</flux:subheading>
                </div>

                <flux:separator />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Customer Details -->
                    <div class="space-y-2">
                        <flux:heading size="sm">{{ __('general.customer_details') }}</flux:heading>
                        <flux:text><strong>{{ __('general.name') }}:</strong> {{ $order->user?->name }}</flux:text>
                        <flux:text><strong>{{ __('general.email') }}:</strong> {{ $order->user?->email }}</flux:text>
                        <flux:text><strong>{{ __('general.national_code') }}:</strong> {{ $order->user?->national_code ?: '-' }}</flux:text>
                    </div>

                    <!-- Shipping Address -->
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
                </div>

                <flux:separator />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Shipping Type -->
                    <div class="space-y-2">
                        <flux:heading size="sm">{{ __('general.shipping_type') }}</flux:heading>
                        <flux:text><strong>{{ __('general.shipping_method') }}:</strong> {{ $order->shippingMethod?->name ?? '-' }}</flux:text>
                        <flux:text><strong>{{ __('general.shipping_zone') }}:</strong> {{ $order->shippingZone?->name ?? '-' }}</flux:text>
                        @if ($order->shipping_estimated_days)
                            <flux:text><strong>{{ __('general.estimated_delivery') }}:</strong> {{ $order->shipping_estimated_days }}</flux:text>
                        @endif
                        <flux:text><strong>{{ __('general.shipping_cost') }}:</strong> {{ number_format((float) $order->shipping_amount) }} {{ $order->currency }}</flux:text>
                    </div>

                    <!-- Payment Info -->
                    <div class="space-y-2">
                        <flux:heading size="sm">{{ __('general.payment_info') }}</flux:heading>
                        <flux:text>
                            <strong>{{ __('general.payment_status') }}:</strong>
                            @if ($order->paid_at)
                                <flux:badge color="green" size="sm">{{ __('general.paid') }}</flux:badge>
                            @else
                                <flux:badge color="orange" size="sm">{{ __('general.unpaid') }}</flux:badge>
                            @endif
                        </flux:text>
                        @if ($order->payment_gateway)
                            <flux:text><strong>{{ __('general.payment_type') }}:</strong> {{ $order->payment_gateway_label ?? '-' }}</flux:text>
                        @endif
                        @if ($order->paid_at)
                            <flux:text><strong>{{ __('general.paid_at') }}:</strong> {{ jalali($order->paid_at) }}</flux:text>
                        @endif
                        @if ($order->resolved_payment_reference_id)
                            <flux:text><strong>{{ __('general.payment_reference_id') }}:</strong> {{ $order->resolved_payment_reference_id }}</flux:text>
                        @endif
                        @if ($order->resolved_payment_transaction_id)
                            <flux:text><strong>{{ __('general.payment_transaction_id') }}:</strong> {{ $order->resolved_payment_transaction_id }}</flux:text>
                        @endif
                        @if ($order->resolved_payment_card_pan)
                            <flux:text><strong>{{ __('general.payment_card_pan') }}:</strong> <span dir="ltr">{{ $order->resolved_payment_card_pan }}</span></flux:text>
                        @endif
                        @if ($order->payment_ip)
                            <flux:text><strong>{{ __('general.payer_ip') }}:</strong> <span dir="ltr">{{ $order->payment_ip }}</span></flux:text>
                        @endif
                    </div>
                </div>

                <flux:separator />

                <!-- Shipping Info -->
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

                <!-- Order Items -->
                <div class="space-y-4">
                    <flux:heading size="sm">{{ __('general.order_items') }}</flux:heading>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('general.name') }}</flux:table.column>
                            <flux:table.column>{{ __('general.sku') }}</flux:table.column>
                            <flux:table.column>{{ __('general.quantity') }}</flux:table.column>
                            <flux:table.column>{{ __('general.unit_price') }}</flux:table.column>
                            <flux:table.column>{{ __('general.total_amount') }}</flux:table.column>
                        </flux:table.columns>

                        @foreach ($order->items as $item)
                            <flux:table.row :key="$item->id">
                                <flux:table.cell>{{ $item->name }}</flux:table.cell>
                                <flux:table.cell>{{ $item->sku }}</flux:table.cell>
                                <flux:table.cell>{{ $item->quantity }}</flux:table.cell>
                                <flux:table.cell>{{ number_format((float) $item->unit_price_amount) }}</flux:table.cell>
                                <flux:table.cell>{{ number_format((float) $item->total_amount) }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table>
                </div>

                <flux:separator />

                <!-- Summary -->
                <div class="flex justify-end">
                    <div class="w-full md:w-1/2 space-y-2 text-left">
                        <div class="flex justify-between">
                            <flux:text>{{ __('general.subtotal') }}:</flux:text>
                            <flux:text>{{ number_format((float) $order->subtotal_amount) }} {{ $order->currency }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text>{{ __('general.discount') }}:</flux:text>
                            <flux:text>{{ number_format((float) $order->discount_amount) }} {{ $order->currency }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text>{{ __('general.tax') }}:</flux:text>
                            <flux:text>{{ number_format((float) $order->tax_amount) }} {{ $order->currency }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text>{{ __('general.shipping_cost') }}:</flux:text>
                            <flux:text>{{ number_format((float) $order->shipping_amount) }} {{ $order->currency }}</flux:text>
                        </div>
                        <flux:separator />
                        <div class="flex justify-between font-bold">
                            <flux:heading size="sm">{{ __('general.total_amount') }}:</flux:heading>
                            <flux:heading size="sm">{{ number_format((float) $order->total_amount) }} {{ $order->currency }}</flux:heading>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    @if ($order->status === 'processing' && $order->paid_at)
                        <flux:button variant="primary" color="green" icon="truck" wire:click="$dispatch('panel.shop.order.ship.assign-data', { id: '{{ $order->id }}' })">
                            {{ __('general.ship_order') }}
                        </flux:button>
                    @endif
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('general.close') }}</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
