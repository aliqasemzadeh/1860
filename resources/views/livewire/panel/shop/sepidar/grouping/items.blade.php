<div>
    <flux:table>
        <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
            <flux:table.column colspan="5" class="bg-white dark:bg-zinc-900">
                <div class="flex flex-col gap-1 pe-2 items-end">
                    <flux:input
                        size="sm"
                        placeholder="{{ __('app.search_placeholder') }}"
                        wire:model.live="search"
                    />
                </div>
            </flux:table.column>
        </flux:table.columns>
        <flux:table.columns>
            <flux:table.column>{{ __('app.name') }}</flux:table.column>
            <flux:table.column>{{ __('app.balance') }}</flux:table.column>
            <flux:table.column>{{ __('app.action') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach($this->items as $item)
                <flux:table.row>
                    <flux:table.cell class="flex items-center gap-3">
                        {{ $item->Title }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @if($lastStockSummary = \App\Models\Sepidar\INV\ItemStockSummary::where('ItemRef', $item->ItemID)->first())
                            {{ number_format($lastStockSummary->Quantity) }}
                        @else
                            0
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:button size="xs" variant="primary" color="sky" wire:click="$dispatch('panel.shop.sepidar.grouping.item.invoice.assign-data', { id: '{{ $item->ItemID }}' })">{{ __('app.invoices') }}</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
