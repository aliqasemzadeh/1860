<flux:modal name="accounting.bank.transaction.create.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.create_transaction') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.create_transaction_description') }}</flux:text>
        </div>
        <form wire:submit="create" method="post">
            <div class="pb-2 space-y-4">
                <flux:field>
                    <flux:label>{{ __('app.transaction_bank') }}</flux:label>
                    <flux:select wire:model="bank_id" placeholder="{{ __('app.select_bank') }}">
                        <flux:select.option value="0">{{ __('app.select_bank') }}</flux:select.option>
                        @foreach ($this->banks as $bank)
                            <flux:select.option value="{{ $bank->id }}">{{ $bank->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="bank_id" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('app.transaction_type') }}</flux:label>
                    <flux:select wire:model="linker" placeholder="{{ __('app.select_transaction_type') }}">
                        <flux:select.option value="">{{ __('app.select_transaction_type') }}</flux:select.option>
                        @foreach ($this->transactionTypes as $type)
                            @php
                                $typeEnum = \App\Enums\TransactionTypeEnum::tryFrom($type);
                            @endphp
                            @if ($typeEnum)
                                <flux:select.option value="{{ $type }}">{{ $typeEnum->label() }}</flux:select.option>
                            @endif
                        @endforeach
                    </flux:select>
                    <flux:error name="linker" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('app.transaction_amount') }}</flux:label>
                    <flux:input wire:model="amount" type="text" mask:dynamic="$money($input)" />
                    <flux:error name="amount" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('app.transaction_description') }}</flux:label>
                    <flux:textarea wire:model="description" rows="3" />
                    <flux:error name="description" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('app.transaction_linker_id') }}</flux:label>
                    <flux:input wire:model="linker_id" type="text" />
                    <flux:error name="linker_id" />
                </flux:field>
            </div>
            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('app.create') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
