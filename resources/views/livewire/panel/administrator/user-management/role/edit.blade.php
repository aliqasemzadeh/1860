<flux:modal name="administrator.user-management.role.edit.modal" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.edit_role') }} : {{ $role->name }}</flux:heading>
            <flux:text class="mt-2">{{ __('general.edit_role_description') }}</flux:text>
        </div>
        <!-- Modal body -->
        <form wire:submit="edit" method="post">
            <div class="pb-2">
                <flux:field>
                    <flux:label>{{ __('general.name') }}</flux:label>

                    <flux:input wire:model="name" type="text" />

                    <flux:error name="name" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('general.guard_name') }}</flux:label>

                    <flux:input wire:model="guard_name" type="text" />

                    <flux:error name="guard_name" />
                </flux:field>
            </div>
            <button type="submit" class="btn-default btn-indigo w-full">
                {{ __('general.update') }}
            </button>
        </form>
    </div>
</flux:modal>
