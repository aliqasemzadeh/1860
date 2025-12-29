<flux:modal name="panel.shop.shipping.method.create.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.create_shipping_method') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.create_shipping_method_description') }}</flux:text>
        </div>

        <form wire:submit="create" method="post">
            <div class="pb-2 space-y-3">
                <flux:field>
                    <flux:label>{{ __('app.name') }}</flux:label>
                    <flux:input wire:model="name" type="text" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.slug') }}</flux:label>
                    <flux:input wire:model="handle" type="text" />
                    <flux:text class="mt-1 text-xs text-gray-500">
                        {{ __('app.shipping_handle_help') }}
                    </flux:text>
                    <flux:error name="handle" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.description') }}</flux:label>
                    <flux:textarea wire:model="description" rows="3" />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.status') }}</flux:label>
                    <flux:switch wire:model="is_active">
                        {{ __('app.is_active') }}
                    </flux:switch>
                    <flux:error name="is_active" />
                </flux:field>
            </div>

            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('app.create') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
