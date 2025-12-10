<flux:modal name="accounting.bank.remittance.edit.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.edit_remittance') }} : {{ isset($id) ? '#' . $id : '' }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.edit_remittance_description') }}</flux:text>
        </div>
        @if (isset($remittance) && !$remittance->checked_at)
            <form wire:submit="edit" method="post">
                <div class="pb-2 space-y-4">
                    <flux:field>
                        <flux:label>{{ __('app.remittance_bank') }}</flux:label>
                        <flux:select wire:model="bank_id" placeholder="{{ __('app.select_bank') }}">
                            <flux:select.option value="0">{{ __('app.select_bank') }}</flux:select.option>
                            @foreach ($this->banks as $bank)
                                <flux:select.option value="{{ $bank->id }}">{{ $bank->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="bank_id" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('app.remittance_description') }}</flux:label>
                        <flux:textarea wire:model="description" rows="3" />
                        <flux:error name="description" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('app.remittance_draft_amount') }}</flux:label>
                        <flux:input wire:model="draft_amount" type="number" step="0.01" min="0" />
                        <flux:error name="draft_amount" />
                    </flux:field>
                </div>
                <flux:button type="submit" class="w-full" variant="primary">
                    {{ __('app.update') }}
                </flux:button>
            </form>
        @elseif (isset($remittance) && $remittance->checked_at)
            <flux:callout variant="warning">
                {{ __('app.remittance_cannot_edit') }}
            </flux:callout>
        @endif
    </div>
</flux:modal>
