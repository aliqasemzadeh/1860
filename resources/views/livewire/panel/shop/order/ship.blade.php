<flux:modal name="panel.shop.order.ship.modal" class="md:w-96" flyout position="right">
    @if ($order)
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('app.ship_order') }}</flux:heading>
                <flux:text class="mt-2">{{ __('app.ship_order_description') }} #{{ $order->order_number }}</flux:text>
            </div>

            <form wire:submit="ship" method="post">
                <div class="pb-2 space-y-3">
                    <flux:field>
                        <flux:label>{{ __('app.tracking_code') }}</flux:label>
                        <flux:input wire:model="trackingCode" type="text" placeholder="{{ __('app.tracking_code_placeholder') }}" />
                        <flux:error name="trackingCode" />
                    </flux:field>
                </div>

                <flux:button type="submit" variant="primary" color="green" class="w-full">
                    {{ __('app.ship_order') }}
                </flux:button>
            </form>
        </div>
    @endif
</flux:modal>
