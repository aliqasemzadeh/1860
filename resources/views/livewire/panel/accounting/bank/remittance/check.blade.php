<flux:modal name="accounting.bank.remittance.check.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.check_remittance') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.check_remittance_description') }}</flux:text>
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
                    <flux:label>{{ __('app.remittance_draft_amount') }}</flux:label>
                    <flux:text>{{ number_format($remittance->draft_amount, 0) }} {{ __('app.toman') }}</flux:text>
                </div>
            </div>
            <form wire:submit="check" method="post">
                <div class="pb-2 space-y-4">
                    <flux:field>
                        <flux:label>{{ __('app.remittance_final_amount') }}</flux:label>
                        <flux:input wire:model="final_amount" type="number" step="0.01" min="0" />
                        <flux:error name="final_amount" />
                    </flux:field>
                </div>
                <flux:button type="submit" class="w-full" variant="primary">
                    {{ __('app.check_remittance_button') }}
                </flux:button>
            </form>
        @endif
    </div>
</flux:modal>
