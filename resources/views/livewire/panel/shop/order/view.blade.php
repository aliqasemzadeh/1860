<div>
    <flux:modal name="panel.shop.order.view.modal" class="md:w-[800px]">
        @if ($order)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('app.order_details') }} #{{ $order->order_number }}</flux:heading>
                    <flux:subheading>{{ __('app.order_date') }}: {{ jalali($order->created_at) }}</flux:subheading>
                </div>

                <flux:separator />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Customer Details -->
                    <div class="space-y-2">
                        <flux:heading size="sm">{{ __('app.customer_details') }}</flux:heading>
                        <flux:text><strong>{{ __('app.name') }}:</strong> {{ $order->user?->name }}</flux:text>
                        <flux:text><strong>{{ __('app.email') }}:</strong> {{ $order->user?->email }}</flux:text>
                        <flux:text><strong>{{ __('app.national_code') }}:</strong> {{ $order->user?->national_code ?: '-' }}</flux:text>
                    </div>

                    <!-- Shipping Address -->
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
                </div>

                <flux:separator />

                <!-- Order Items -->
                <div class="space-y-4">
                    <flux:heading size="sm">{{ __('app.order_items') }}</flux:heading>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('app.name') }}</flux:table.column>
                            <flux:table.column>{{ __('app.sku') }}</flux:table.column>
                            <flux:table.column>{{ __('app.quantity') }}</flux:table.column>
                            <flux:table.column>{{ __('app.unit_price') }}</flux:table.column>
                            <flux:table.column>{{ __('app.total_amount') }}</flux:table.column>
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
                            <flux:text>{{ __('app.subtotal') }}:</flux:text>
                            <flux:text>{{ number_format((float) $order->subtotal_amount) }} {{ $order->currency }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text>{{ __('app.discount') }}:</flux:text>
                            <flux:text>{{ number_format((float) $order->discount_amount) }} {{ $order->currency }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text>{{ __('app.tax') }}:</flux:text>
                            <flux:text>{{ number_format((float) $order->tax_amount) }} {{ $order->currency }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text>{{ __('app.shipping_cost') }}:</flux:text>
                            <flux:text>{{ number_format((float) $order->shipping_amount) }} {{ $order->currency }}</flux:text>
                        </div>
                        <flux:separator />
                        <div class="flex justify-between font-bold">
                            <flux:heading size="sm">{{ __('app.total_amount') }}:</flux:heading>
                            <flux:heading size="sm">{{ number_format((float) $order->total_amount) }} {{ $order->currency }}</flux:heading>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('app.close') }}</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
