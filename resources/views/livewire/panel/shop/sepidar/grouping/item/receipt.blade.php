<flux:modal name="panel.shop.sepidar.grouping.item.receipt.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.receipts') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.receipts_description') }}</flux:text>
        </div>

        @foreach($this->buys as $buy)
            {{ number_format($buy->Fee) }} - {{ \Morilog\Jalali\Jalalian::fromDateTime($buy->receipt->CreationDate) }}
            <br />
        @endforeach
    </div>
</flux:modal>
