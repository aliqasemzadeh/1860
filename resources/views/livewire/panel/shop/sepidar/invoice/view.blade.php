<flux:modal name="panel.shop.sepidar.invoice.view.modal" class="md:w-1/3" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.invoice_items') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.invoice_items_description') }}</flux:text>
        </div>
        <flux:table>
            <flux:table.columns class="bg-white dark:bg-zinc-900">
                <flux:table.column>{{ __('app.name') }}</flux:table.column>
                <flux:table.column>{{ __('app.fee') }}</flux:table.column>
                <flux:table.column>{{ __('app.quantity') }}</flux:table.column>
                <flux:table.column>{{ __('app.price') }}</flux:table.column>
                <flux:table.column>{{ __('app.last_buy_fee') }}</flux:table.column>
                <flux:table.column>{{ __('app.last_buy_price') }}</flux:table.column>
                <flux:table.column>{{ __('app.profit') }}</flux:table.column>
            </flux:table.columns>
            @if(isset($invoice))

            <flux:table.rows>
                @foreach($invoice->items as $item)
                    @php
                        $lastReceipt = \App\Models\Sepidar\INV\InventoryReceiptItem::query()
                            ->joinWhere('sepidar_inventory_receipts', 'InventoryReceiptRef', '=', 'InventoryReceiptID')
                            ->where('ItemRef', $item->ItemId)
                            ->latest('CreationDate')
                            ->first();
                    @endphp
                    <flux:table.row>
                        <flux:table.cell>
                            {{ $item->item->Title }}
                            {{ dd($lastReceipt) }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ number_format($item->Fee) }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ number_format($item->Quantity) }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ number_format($item->Price) }}
                        </flux:table.cell>

                        <flux:table.cell>

                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
                @endif
        </flux:table>
    </div>
</flux:modal>
