<x-slot name="title">
    {{ __('general.users') }}
</x-slot>
<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.users') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.users_description') }}</flux:subheading>
            </div>
            @can('administrator_user_management_create')
                <flux:modal.trigger name="administrator.user-management.user.create.modal">
                    <flux:button variant="primary">{{ __('general.create_user') }}</flux:button>
                </flux:modal.trigger>
            @endcan
        </div>

        <flux:separator variant="subtle" />
    </div>
    <livewire:panel.administrator.user-management.user.create />
    <livewire:panel.administrator.user-management.user.edit />
    <livewire:panel.administrator.user-management.user.roles />
    <livewire:panel.administrator.user-management.user.permissions />

    <flux:table :paginate="$this->users">
        <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
            <flux:table.column colspan="4" class="bg-white dark:bg-zinc-900">
                <div class="flex flex-col gap-1 pe-2 items-end">
                    <flux:input
                        size="sm"
                        placeholder="{{ __('general.search_placeholder') }}"
                        wire:model.live="search"
                    />
                </div>
            </flux:table.column>
        </flux:table.columns>
        <flux:table.columns>
            <flux:table.column>{{ __('general.mobile') }}</flux:table.column>
            <flux:table.column>{{ __('general.name') }}</flux:table.column>
            <flux:table.column sortable sorted direction="desc">{{ __('general.date') }}</flux:table.column>
            <flux:table.column>{{ __('general.options') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->users as $user)
                <flux:table.row :key="$user->id">
                    <flux:table.cell class="flex items-center gap-3">
                        {{ $user->mobile }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $user->name }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ jalali($user->created_at) }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        @can('administrator_user_management_edit')
                            <flux:button size="xs" variant="primary" wire:click="$dispatch('panel.administrator.user-management.user.edit.assign-data', { id: '{{ $user->id }}' })">{{ __('general.edit') }}</flux:button>
                        @endcan
                        @can('administrator_user_management_roles')
                            <flux:button size="xs" variant="primary" color="orange" wire:click="$dispatch('panel.administrator.user-management.user.roles.assign-data', { id: '{{ $user->id }}' })">{{ __('general.roles') }}</flux:button>
                        @endcan
                        @can('administrator_user_management_permissions')
                            <flux:button size="xs" variant="primary" color="lime" wire:click="$dispatch('panel.administrator.user-management.user.permissions.assign-data', { id: '{{ $user->id }}' })">{{ __('general.permissions') }}</flux:button>
                        @endcan
                        @can('administrator_user_management_delete')
                            <flux:button size="xs" variant="danger">{{ __('general.delete') }}</flux:button>
                        @endcan
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
