<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.users') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.users_description') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="administrator.user-management.user.create.modal">
                <flux:button variant="primary">{{ __('app.create_user') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>
    <livewire:administrator.user-management.user.create />
    <livewire:administrator.user-management.user.edit />
    <livewire:administrator.user-management.user.roles />
    <livewire:administrator.user-management.user.permissions />

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('app.customer') }}</flux:table.column>
            <flux:table.column sortable sorted direction="desc">{{ __('app.date') }}</flux:table.column>
        </flux:table.columns>
        @foreach ($this->users as $user)
            <flux:table.row :key="$user->id">
                <flux:table.cell class="flex items-center gap-3">
                    {{ $user->mobile }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <flux:button size="xs" variant="primary" wire:click="$dispatch('administrator.user-management.user.edit.assign-data', { id: '{{ $user->id }}' })">{{ __('app.edit') }}</flux:button>
                    <flux:button size="xs" variant="primary" color="orange" wire:click="$dispatch('administrator.user-management.user.roles.assign-data', { id: '{{ $user->id }}' })">{{ __('app.roles') }}</flux:button>
                    <flux:button size="xs" variant="primary" color="lime" wire:click="$dispatch('administrator.user-management.user.permissions.assign-data', { id: '{{ $user->id }}' })">{{ __('app.permissions') }}</flux:button>
                    <flux:button size="xs" variant="danger">{{ __('app.delete') }}</flux:button>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
