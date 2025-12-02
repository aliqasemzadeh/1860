<flux:modal name="administrator.user-management.user.create.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('app.create_user') }}</flux:heading>
        <flux:text class="mt-2">{{ __('app.create_user_description') }}</flux:text>
    </div>

            <!-- Modal body -->
            <form wire:submit="create" method="post">
                <div class="pb-2">
                    <flux:field>
                        <flux:field>
                            <flux:label>{{ __('app.name') }}</flux:label>

                            <flux:input wire:model="name" type="text" />

                            <flux:error name="name" />
                        </flux:field>
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('app.email') }}</flux:label>

                        <flux:input wire:model="email" type="email" />

                        <flux:error name="email" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('app.password') }}</flux:label>

                        <flux:input wire:model="password" type="password" />

                        <flux:error name="password" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('app.password_confirmation') }}</flux:label>

                        <flux:input wire:model="password_confirmation" type="password" />

                        <flux:error name="password_confirmation" />
                    </flux:field>
                </div>
                <flux:button type="submit" class="w-full" variant="primary">
                    {{ __('app.create') }}
                </flux:button>
            </form>
    </div>
</flux:modal>
