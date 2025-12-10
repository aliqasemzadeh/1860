<flux:modal name="accounting.bank.edit.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.edit_bank') }} : {{ isset($name) ? $name : '' }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.edit_bank_description') }}</flux:text>
        </div>
        <form wire:submit="edit" method="post">
            <div class="pb-2 space-y-4">
                <flux:field>
                    <flux:label>{{ __('app.bank_name') }}</flux:label>
                    <flux:input wire:model="name" type="text" />
                    <flux:error name="name" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('app.bank_description') }}</flux:label>
                    <flux:textarea wire:model="description" rows="3" />
                    <flux:error name="description" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('app.sort_order') }}</flux:label>
                    <flux:input wire:model="sort_order" type="number" min="0" />
                    <flux:error name="sort_order" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('app.bank_init_balance') }}</flux:label>
                    <flux:input wire:model="init_balance" type="text" mask:dynamic="$money($input)" />
                    <flux:error name="init_balance" />
                </flux:field>
            </div>
            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('app.update') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
