<flux:modal name="accounting.remittance.create.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.create_remittance') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.create_remittance_description') }}</flux:text>
        </div>
        <form wire:submit="create" method="post">
            <div class="pb-2 space-y-4">
                <flux:field>
                    <flux:label>{{ __('app.remittance_description') }}</flux:label>
                    <flux:textarea wire:model="description" rows="3" />
                    <flux:error name="description" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('app.account_balance') }}</flux:label>
                    <flux:input wire:model="account_balance" type="text" mask:dynamic="$money($input)" />
                    <flux:error name="account_balance" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('app.remittance_amount') }}</flux:label>
                    <flux:input wire:model="payment" type="text" mask:dynamic="$money($input)" />
                    <flux:error name="payment" />
                </flux:field>
            </div>
            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('app.create') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
