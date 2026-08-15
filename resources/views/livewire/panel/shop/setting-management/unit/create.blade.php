<flux:modal name="panel.shop.setting-management.unit.create.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.create_unit') }}</flux:heading>
            <flux:text class="mt-2">{{ __('general.create_unit_description') }}</flux:text>
        </div>

        <form wire:submit="create" method="post">
            <div class="pb-2 space-y-3">
                <flux:field>
                    <flux:label>{{ __('general.name') }}</flux:label>
                    <flux:input wire:model="name" type="text" />
                    <flux:error name="name" />
                </flux:field>
            </div>

            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('general.create') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
