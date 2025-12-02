<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.roles') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.roles_description') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="administrator.user-management.role.create.modal">
                <flux:button variant="primary">{{ __('app.create_role') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:administrator.user-management.role.create />
    <livewire:administrator.user-management.role.edit />
    <livewire:administrator.user-management.role.permissions />
    <livewire:administrator.user-management.role.users />

    <flux:table>
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('app.name') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'guard_name'" :direction="$sortDirection" wire:click="sort('guard_name')">{{ __('app.guard_name') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('app.date') }}</flux:table.column>
            <flux:table.column />
        </flux:table.columns>

        @foreach ($this->roles as $role)
            <flux:table.row :key="$role->id">
                <flux:table.cell class="whitespace-nowrap">{{ $role->name }}</flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">{{ $role->guard_name }}</flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">{{ $role->created_at?->format('Y-m-d H:i') }}</flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <flux:button size="xs" variant="primary" wire:click="$dispatch('administrator.user-management.role.edit.assign-data', { id: '{{ $role->id }}' })">{{ __('app.edit') }}</flux:button>
                    <flux:button size="xs" variant="primary" wire:click="$dispatch('administrator.user-management.role.users.assign-data', { id: '{{ $role->id }}' })">{{ __('app.users') }}</flux:button>
                    <flux:button size="xs" variant="primary" wire:click="$dispatch('administrator.user-management.role.permissions.assign-data', { id: '{{ $role->id }}' })">{{ __('app.permissions') }}</flux:button>
                    <flux:button size="xs" variant="danger">{{ __('app.delete') }}</flux:button>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
