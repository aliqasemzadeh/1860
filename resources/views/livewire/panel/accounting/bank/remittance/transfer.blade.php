<flux:modal name="accounting.bank.remittance.transfer.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.transfer_remittance') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.transfer_remittance_description') }}</flux:text>
        </div>
        @if (isset($remittance))
            <div class="space-y-2 pb-2">
                <div>
                    <flux:label>{{ __('app.remittance_bank') }}</flux:label>
                    <flux:text>{{ $remittance->bank->name }}</flux:text>
                </div>
                <div>
                    <flux:label>{{ __('app.remittance_description') }}</flux:label>
                    <flux:text>{{ $remittance->description }}</flux:text>
                </div>
                <div>
                    <flux:label>{{ __('app.remittance_final_amount') }}</flux:label>
                    <flux:text>{{ number_format($remittance->final_amount, 0) }} {{ __('app.toman') }}</flux:text>
                </div>
            </div>
            <form wire:submit="transfer" method="post">
                <flux:button type="submit" class="w-full" variant="primary" color="blue">
                    {{ __('app.transfer_remittance_button') }}
                </flux:button>
            </form>
        @endif
    </div>
</flux:modal>
