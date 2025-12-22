<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.repairs') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.repairs_description') }}</flux:subheading>
            </div>
            @can('service_center_repair_create')
            <flux:modal.trigger name="service-center.repair.admission.modal">
                <flux:button variant="primary">{{ __('app.admission') }}</flux:button>
            </flux:modal.trigger>
            @endcan
        </div>

        <flux:separator variant="subtle" />
    </div>

    @can('service_center_repair_create')
    <livewire:service-center.repair.create />
    @endcan
    @can('service_center_repair_view')
    <livewire:service-center.repair.problem />
    <livewire:service-center.repair.view />
    @endcan
    @can('service_center_repair_services')
    <livewire:service-center.repair.services />
    @endcan
    @can('service_center_repair_logs')
    <livewire:service-center.repair.logs />
    @endcan
    @can('service_center_repair_edit')
        <livewire:service-center.repair.edit />
    @endcan

    <flux:table :paginate="$this->repairs">
        <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
            <flux:table.column colspan="8" class="bg-white dark:bg-zinc-900">
                <div class="flex flex-col gap-1 pe-2 items-end">
                    <flux:input
                        size="sm"
                        placeholder="{{ __('app.search_placeholder') }}"
                        wire:model.live="search"
                    />
                </div>
            </flux:table.column>
        </flux:table.columns>
        <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
            <flux:table.column class="bg-white dark:bg-zinc-900">
                    <span>{{ __('app.id') }}</span>
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
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('app.date') }}</flux:table.column>
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
                    <flux:table.cell class="flex flex-col">
                        {{ $repair->owner_name }}
                        @if($repair->owner_organization)
                            <br />
                            {{ $repair->owner_organization }}
                        @endif


                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $repair->owner_mobile }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @php
                            $statusEnum = \App\Enums\StatusEnum::tryFromSafe($repair->status);
                        @endphp
                        <flux:badge variant="solid" color="{{ $statusEnum->color() }}">
                            {{ $statusEnum->label() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $repair->device_type }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $repair->device_serial_number }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ \Morilog\Jalali\Jalalian::fromCarbon($repair->created_at)->format('%Y-%m-%d %H:%M') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @can('service_center_repair_view')
                        <flux:button
                            size="xs"
                            variant="primary"
                            wire:click="$dispatch('service-center.repair.view.assign-data', { id: {{ $repair->id }} })"
                        >
                            {{ __('app.view') }}
                        </flux:button>
                        <flux:button
                            size="xs"
                            variant="danger"
                            wire:click="$dispatch('service-center.repair.problem.assign-data', { id: {{ $repair->id }} })"
                        >
                            {{ __('app.problem') }}
                        </flux:button>
                        @endcan
                        @can('service_center_repair_services')
                        <flux:button size="xs" variant="primary" color="orange"
                                     wire:click="$dispatch('service-center.repair.services.assign-data', { id: {{ $repair->id }} })"
                        >
                            {{ __('app.services') }}
                        </flux:button>
                        @endcan
                        @can('service_center_repair_logs')
                        <flux:button size="xs" variant="primary" color="lime"
                                     wire:click="$dispatch('service-center.repair.logs.assign-data', { id: {{ $repair->id }} })"
                        >
                            {{ __('app.logs') }}
                        </flux:button>
                        @endcan

                            @can('service_center_repair_edit')
                                <flux:button size="xs" variant="primary" color="blue"
                                             wire:click="$dispatch('service-center.repair.edit.assign-data', { id: {{ $repair->id }} })"
                                >
                                    {{ __('app.edit') }}
                                </flux:button>
                            @endcan
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
