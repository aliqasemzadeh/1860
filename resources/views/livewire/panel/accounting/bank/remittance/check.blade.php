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
                    <flux:label>{{ __('app.bank_balance') }}</flux:label>
                    <flux:text class="{{ $remittance->bank->calculateBalance() < $remittance->final_amount ? 'text-red-600 font-bold' : '' }}">
                        {{ number_format($remittance->bank->calculateBalance(), 0) }} {{ __('app.toman') }}
                    </flux:text>
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
                        <flux:input wire:model="final_amount" type="text" mask:dynamic="$money($input)" />
                        <flux:error name="final_amount" />
                    </flux:field>
                </div>
                <div class="flex gap-2">
                    <flux:button type="submit" class="flex-1" variant="primary">
                        {{ __('app.check_remittance_button') }}
                    </flux:button>
                    <flux:button type="button" wire:click="reject" class="flex-1" variant="danger" color="red" wire:confirm="{{ __('app.are_you_sure') }}">
                        {{ __('app.reject') }}
                    </flux:button>
                </div>
            </form>
        @endif
    </div>
</flux:modal>
