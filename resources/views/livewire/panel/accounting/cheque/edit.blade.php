<flux:modal name="accounting.cheque.edit.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ __('app.edit_cheque') }} : {{ isset($id) ? '#' . $id : '' }}
            </flux:heading>
            <flux:text class="mt-2">{{ __('app.edit_cheque_description') }}</flux:text>
        </div>
        @if (isset($cheque))
            <form wire:submit="edit" method="post">
                <div class="pb-2 space-y-4">
                    <flux:field>
                        <flux:label>{{ __('app.cheque_description') }}</flux:label>
                        <flux:textarea wire:model="description" rows="3" />
                        <flux:error name="description" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('app.cheque_amount') }}</flux:label>
                        <flux:input wire:model="amount" type="text" mask:dynamic="$money($input)" />
                        <flux:error name="amount" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('app.cheque_due_at') }}</flux:label>
                        <flux:input
                            wire:model="due_at"
                            type="text"
                            placeholder="1403/01/01"
                            mask="9999/99/99"
                        />
                        <flux:error name="due_at" />
                    </flux:field>
                </div>
                <flux:button type="submit" class="w-full" variant="primary">
                    {{ __('app.update') }}
                </flux:button>
            </form>
        @endif
    </div>
</flux:modal>
