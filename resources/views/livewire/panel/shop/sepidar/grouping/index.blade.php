<div>
    @foreach($mainGrouping as $grouping)
        {{ $grouping->Title }}
        <br />
        @php
            $grouings = \App\Models\Sepidar\GNR\Grouping::where('ParentGroupRef', $grouping->GroupingID)->get();
        @endphp
        <div class="p-4">
            @foreach($grouings as $sub_grouping)
                {{ $sub_grouping->Title }} ({{ $sub_grouping->GroupingID }})
                <br />
                @php
                    $items = \App\Models\Sepidar\INV\Item::where('CodingGroupRef', $sub_grouping->GroupingID)->get();
                @endphp
                <div class="p-4">
                    @foreach($items as $item)
                        {{ $item->Title }} ({{ $item->ItemID }})
                        <br />
                    @endforeach
                </div>
            @endforeach
        </div>
    @endforeach
</div>
