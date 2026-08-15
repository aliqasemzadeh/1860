<div>
    <x-slot name="title">
        {{ __('general.boxes') }} - {{ config('app.name') }}
    </x-slot>

    <div class="relative mb-6 w-full">
        <flux:breadcrumbs class="mb-6">
            <flux:breadcrumbs.item href="{{ route('panel.content.post.index') }}" wire:navigate>{{ __('general.content') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ __('general.boxes') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.boxes') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.manage_boxes_description') }}</flux:subheading>
            </div>

            @can('content_box_create')
                <flux:modal.trigger name="content.box.create">
                    <flux:button variant="primary" color="teal" icon="plus">
                        {{ __('general.create_box') }}
                    </flux:button>
                </flux:modal.trigger>
            @endcan
        </div>

        <flux:separator variant="subtle" />
    </div>

    <flux:card class="mb-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('general.search') }}</flux:label>
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable />
            </flux:field>
        </div>
    </flux:card>

    <flux:table :paginate="$this->boxes">
        <flux:table.columns>
            <flux:table.column>{{ __('general.title') }}</flux:table.column>
            <flux:table.column>{{ __('general.slug') }}</flux:table.column>
            <flux:table.column>{{ __('general.status') }}</flux:table.column>
            <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->boxes as $box)
                <flux:table.row :key="$box->id">
                    <flux:table.cell>{{ $box->title_fa }}</flux:table.cell>
                    <flux:table.cell>{{ $box->title_en }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="{{ $box->is_active ? 'green' : 'zinc' }}" size="sm">
                            {{ $box->is_active ? __('general.active') : __('general.inactive') }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-2">
                            @can('content_box_edit')
                                <flux:tooltip content="{{ __('general.products') }}">
                                    <flux:button size="xs" variant="primary" color="sky" icon="package" icon:variant="outline" wire:click="$dispatch('panels.administrator.content.box.products.assign-data', { id: {{ $box->id }} })" />
                                </flux:tooltip>
                                <flux:tooltip content="{{ __('general.edit') }}">
                                    <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.administrator.content.box.edit.assign-data', { id: {{ $box->id }} })" />
                                </flux:tooltip>
                            @endcan
                            @can('content_box_delete')
                                <flux:tooltip content="{{ __('general.delete') }}">
                                    <flux:modal.trigger name="content.box.delete.{{ $box->id }}">
                                        <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" />
                                    </flux:modal.trigger>
                                </flux:tooltip>
                            @endcan
                        </div>

                        <flux:modal name="content.box.delete.{{ $box->id }}" class="min-w-[22rem]">
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>
                                    <flux:text class="mt-2">
                                        {{ __('general.delete_warning_message') }}<br>
                                        {{ __('general.action_cannot_be_reversed') }}
                                    </flux:text>
                                </div>
                                <div class="flex gap-2">
                                    <flux:spacer />
                                    <flux:modal.close>
                                        <flux:button variant="ghost">{{ __('general.cancel') }}</flux:button>
                                    </flux:modal.close>
                                    <flux:button type="submit" variant="danger" wire:click="delete({{ $box->id }})">{{ __('general.delete') }}</flux:button>
                                </div>
                            </div>
                        </flux:modal>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <livewire:panel.content.box.create :key="'box-create-modal'" />
    <livewire:panel.content.box.edit :key="'box-edit-modal'" />
    <livewire:panel.content.box.products :key="'box-products-modal'" />
</div>
