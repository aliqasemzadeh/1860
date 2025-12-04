<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.repairs') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.repairs_description') }}</flux:subheading>
            </div>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <flux:table :paginate="$this->repairs">
        <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
            <flux:table.column class="bg-white dark:bg-zinc-900">
                <div class="flex flex-col gap-1">
                    <span>{{ __('app.id') }}</span>
                    <flux:input
                        size="xs"
                        placeholder="{{ __('app.search_placeholder') }}"
                        wire:model.debounce.500ms="search"
                    />
                </div>
            </flux:table.column>
            <flux:table.column class="bg-white dark:bg-zinc-900">
                <span>{{ __('app.owner_name') }}</span>
            </flux:table.column>
            <flux:table.column class="bg-white dark:bg-zinc-900">
                <span>{{ __('app.owner_mobile') }}</span>
            </flux:table.column>
            <flux:table.column class="bg-white dark:bg-zinc-900">
                <span>{{ __('app.status') }}</span>
            </flux:table.column>
            <flux:table.column class="bg-white dark:bg-zinc-900">
                <span>{{ __('app.device_type') }}</span>
            </flux:table.column>
            <flux:table.column class="bg-white dark:bg-zinc-900">
                <span>{{ __('app.device_serial_number') }}</span>
            </flux:table.column>
            <flux:table.column class="bg-white dark:bg-zinc-900">
                <span>{{ __('app.options') }}</span>
            </flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->repairs as $repair)
                <flux:table.row :key="$repair->id">
                    <flux:table.cell class="whitespace-nowrap">
                        {{ $repair->id }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $repair->owner_name }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $repair->owner_mobile }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $repair->status }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $repair->device_type }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $repair->device_serial_number }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:button size="xs" variant="primary">
                            {{ __('app.view') }}
                        </flux:button>
                        <flux:button size="xs" variant="primary" color="orange">
                            {{ __('app.services') }}
                        </flux:button>
                        <flux:button size="xs" variant="primary" color="lime">
                            {{ __('app.logs') }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
