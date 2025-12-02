<div>
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('app.users') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('app.users_description') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>
    <livewire:administrator.user-management.user.create />
    <livewire:administrator.user-management.user.edit />
    <livewire:administrator.user-management.user.roles />
    <livewire:administrator.user-management.user.permissions />

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Customer</flux:table.column>
            <flux:table.column sortable sorted direction="desc">Date</flux:table.column>
        </flux:table.columns>
        @foreach ($this->users as $user)
            <flux:table.row :key="$user->id">
                <flux:table.cell class="flex items-center gap-3">
                    {{ $user->mobile }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <flux:button size="xs" variant="primary" wire:click="$dispatch('administrator.user-management.user.edit.assign-data', { id: '{{ $user->id }}' })">ویرایش</flux:button>
                    <flux:button size="xs" variant="danger">حذف</flux:button>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
