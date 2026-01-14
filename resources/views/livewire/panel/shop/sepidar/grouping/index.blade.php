<div>
    @foreach($mainGrouping as $groupingItem)
        <flux:button variant="primary" wire:navigate href="{{ route('panel.shop.sepidar.grouping.index', ['groupingId' => $groupingItem->id]) }}">{{ $groupingItem->Title }}</flux:button>
    @endforeach


        <br />
        @php
            $grouings = \App\Models\Sepidar\GNR\Grouping::where('ParentGroupRef', $grouping->GroupingID)->get();
        @endphp
        <flux:accordion>
            @foreach($grouings as $sub_grouping)
            <flux:accordion.item>
                <flux:accordion.heading>{{ $sub_grouping->Title }}</flux:accordion.heading>
                <flux:accordion.content>
                    @php
                        $items = \App\Models\Sepidar\INV\Item::where('CodingGroupRef', $sub_grouping->GroupingID)->get();
                    @endphp
                    <div class="p-4">


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
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach($items as $item)
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
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>

                </flux:accordion.content>
            </flux:accordion.item>
            @endforeach
        </flux:accordion>
</div>
