<flux:modal name="panel.shop.sepidar.grouping.item.invoice.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.invoices') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.invoices_description') }}</flux:text>
        </div>
        @foreach($this->sales as $sale)
            {{ number_format($sale->Fee) }} - {{ \Morilog\Jalali\Jalalian::fromDateTime($sale->invoice->CreationDate) }}
            <br />
        @endforeach
    </div>
</flux:modal>
