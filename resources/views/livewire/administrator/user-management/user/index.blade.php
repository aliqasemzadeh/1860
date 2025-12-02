<div>
    <flux:heading size="xl" level="1">Good afternoon, Olivia</flux:heading>
    <flux:text class="mb-6 mt-2 text-base">Here's what's new today</flux:text>
    <flux:separator variant="subtle" />

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
                <flux:table.cell class="whitespace-nowrap">{{ $user->created_at }}</flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table>
</div>
