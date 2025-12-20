<flux:modal name="accounting.bank.create.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.create_bank') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.create_bank_description') }}</flux:text>
        </div>
        <!-- Modal body -->
        <form wire:submit="create" method="post">
            <div class="pb-2 space-y-4">
                <flux:field>
                    <flux:label>{{ __('app.bank_name') }}</flux:label>
                    <flux:input wire:model="name" type="text" />
                    <flux:error name="name" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('app.bank_code') }}</flux:label>
                    <flux:select wire:model="code" variant="listbox" searchable placeholder="{{ __('app.select_bank_code') }}">
                        <flux:select.option value="">{{ __('app.none') }}</flux:select.option>
                        @foreach (__('banks') as $key => $value)
                            <flux:select.option value="{{ $key }}">{{ $value }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="code" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('app.bank_number') }}</flux:label>
                    <flux:input wire:model="number" type="text" />
                    <flux:error name="number" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('app.bank_iban') }}</flux:label>
                    <flux:input wire:model="iban" type="text" />
                    <flux:error name="iban" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('app.bank_card_number') }}</flux:label>
                    <flux:input wire:model="card_number" type="text" />
                    <flux:error name="card_number" />
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
                    <flux:label>{{ __('app.bank_balance') }}</flux:label>
                    <flux:input wire:model="init_balance" mask:dynamic="$money($input)" type="text" />
                    <flux:error name="init_balance" />
                </flux:field>
            </div>
            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('app.create') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
