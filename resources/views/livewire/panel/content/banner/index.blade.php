<div>
    <x-slot name="title">
        {{ __('general.banners') }} - {{ config('app.name') }}
    </x-slot>

    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.banners') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.banners_description') }}</flux:subheading>
            </div>
            @can('content_banner_create')
                <flux:modal.trigger name="panel.content.banner.create.modal">
                    <flux:button variant="primary" color="teal" icon="plus">{{ __('general.create_banner') }}</flux:button>
                </flux:modal.trigger>
            @endcan
        </div>

        <flux:separator variant="subtle" />
    </div>

    <flux:card class="mb-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <flux:field>
                <flux:label>{{ __('general.search') }}</flux:label>
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    clearable
                    icon="search"
                    type="text"
                    placeholder="{{ __('general.search_in_banners') }}"
                />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('general.status') }}</flux:label>
                <flux:select wire:model.live="statusFilter" searchable placeholder="{{ __('general.status') }}">
                    <flux:select.option value="">{{ __('general.all_statuses') }}</flux:select.option>
                    <flux:select.option value="active">{{ __('general.active') }}</flux:select.option>
                    <flux:select.option value="inactive">{{ __('general.inactive') }}</flux:select.option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('general.trashed_filter') }}</flux:label>
                <flux:select wire:model.live="trashedFilter" searchable>
                    <flux:select.option value="without">{{ __('general.without_trashed') }}</flux:select.option>
                    <flux:select.option value="with">{{ __('general.with_trashed') }}</flux:select.option>
                    <flux:select.option value="only">{{ __('general.only_trashed') }}</flux:select.option>
                </flux:select>
            </flux:field>
        </div>
    </flux:card>

    <livewire:panel.content.banner.create :key="'banner-create-modal'" />
    <livewire:panel.content.banner.edit :key="'banner-edit-modal'" />

    <flux:table :paginate="$this->banners">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'sort_order'" :direction="$sortDirection" wire:click="sort('sort_order')">
                {{ __('general.sort_order') }}
            </flux:table.column>
            <flux:table.column>{{ __('general.image') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'title'" :direction="$sortDirection" wire:click="sort('title')">
                {{ __('general.title') }}
            </flux:table.column>
            <flux:table.column>{{ __('general.banner_link') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'is_active'" :direction="$sortDirection" wire:click="sort('is_active')">
                {{ __('general.status') }}
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">
                {{ __('general.date') }}
            </flux:table.column>
            <flux:table.column align="end">{{ __('general.options') }}</flux:table.column>
        </flux:table.columns>

        @forelse ($this->banners as $banner)
            <flux:table.row :key="$banner->id">
                <flux:table.cell class="whitespace-nowrap">
                    <div class="flex items-center gap-1.5">
                        <flux:badge size="sm" color="zinc">{{ $banner->sort_order }}</flux:badge>

                        @if (! $banner->trashed())
                            @can('content_banner_edit')
                                <flux:tooltip content="{{ __('general.move_up') }}">
                                    <flux:button
                                        size="xs"
                                        variant="ghost"
                                        icon="chevron-up"
                                        wire:click="moveUp({{ $banner->id }})"
                                    />
                                </flux:tooltip>
                                <flux:tooltip content="{{ __('general.move_down') }}">
                                    <flux:button
                                        size="xs"
                                        variant="ghost"
                                        icon="chevron-down"
                                        wire:click="moveDown({{ $banner->id }})"
                                    />
                                </flux:tooltip>
                            @endcan
                        @endif
                    </div>
                </flux:table.cell>

                <flux:table.cell>
                    <div class="flex items-center gap-2">
                        @if ($banner->desktop_image_url)
                            <img
                                src="{{ $banner->desktop_image_url }}"
                                alt="{{ $banner->title }}"
                                class="h-12 w-24 rounded-lg border border-zinc-200 object-cover dark:border-zinc-700"
                            />
                        @endif
                        @if ($banner->mobile_image_url)
                            <img
                                src="{{ $banner->mobile_image_url }}"
                                alt="{{ $banner->title }}"
                                class="h-12 w-9 rounded-lg border border-zinc-200 object-cover dark:border-zinc-700"
                                title="{{ __('general.mobile_image') }}"
                            />
                        @endif
                    </div>
                </flux:table.cell>

                <flux:table.cell>
                    <div class="font-medium">{{ $banner->title }}</div>
                    @if ($banner->trashed())
                        <flux:badge color="red" size="sm" class="mt-1">{{ __('general.only_trashed') }}</flux:badge>
                    @endif
                </flux:table.cell>

                <flux:table.cell>
                    @if ($banner->link)
                        <a
                            href="{{ $banner->link }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            dir="ltr"
                            class="inline-flex max-w-xs items-center gap-1 truncate text-xs text-teal-600 hover:underline dark:text-teal-400"
                        >
                            <span class="truncate">{{ $banner->link }}</span>
                            <flux:icon.arrow-top-right-on-square class="size-3.5 shrink-0" />
                        </a>
                    @else
                        <span class="text-zinc-400">-</span>
                    @endif
                </flux:table.cell>

                <flux:table.cell class="whitespace-nowrap">
                    @can('content_banner_edit')
                        <button type="button" wire:click="toggleActive({{ $banner->id }})" class="cursor-pointer">
                            <flux:badge color="{{ $banner->is_active ? 'green' : 'zinc' }}" size="sm">
                                {{ $banner->is_active ? __('general.active') : __('general.inactive') }}
                            </flux:badge>
                        </button>
                    @else
                        <flux:badge color="{{ $banner->is_active ? 'green' : 'zinc' }}" size="sm">
                            {{ $banner->is_active ? __('general.active') : __('general.inactive') }}
                        </flux:badge>
                    @endcan
                </flux:table.cell>

                <flux:table.cell class="whitespace-nowrap">
                    {{ jalali($banner->created_at) }}
                </flux:table.cell>

                <flux:table.cell align="end" class="whitespace-nowrap">
                    <div class="flex items-center justify-end gap-2">
                        @if ($banner->trashed())
                            @can('content_banner_edit')
                                <flux:tooltip content="{{ __('general.restore') }}">
                                    <flux:button
                                        size="xs"
                                        variant="primary"
                                        color="emerald"
                                        icon="arrow-uturn-left"
                                        icon:variant="outline"
                                        wire:click="restore({{ $banner->id }})"
                                    />
                                </flux:tooltip>
                            @endcan

                            @can('content_banner_delete')
                                <flux:tooltip content="{{ __('general.force_delete') }}">
                                    <flux:button
                                        size="xs"
                                        variant="primary"
                                        color="red"
                                        icon="trash"
                                        icon:variant="outline"
                                        wire:click="forceDelete({{ $banner->id }})"
                                        wire:confirm="{{ __('general.are_you_sure') }}"
                                    />
                                </flux:tooltip>
                            @endcan
                        @else
                            @can('content_banner_edit')
                                <flux:tooltip content="{{ __('general.edit') }}">
                                    <flux:button
                                        size="xs"
                                        variant="primary"
                                        color="blue"
                                        icon="pencil"
                                        icon:variant="outline"
                                        wire:click="$dispatch('panel.content.banner.edit.assign-data', { id: {{ $banner->id }} })"
                                    />
                                </flux:tooltip>
                            @endcan

                            @can('content_banner_delete')
                                <flux:tooltip content="{{ __('general.delete') }}">
                                    <flux:button
                                        size="xs"
                                        variant="primary"
                                        color="red"
                                        icon="trash"
                                        icon:variant="outline"
                                        wire:click="delete({{ $banner->id }})"
                                        wire:confirm="{{ __('general.are_you_sure') }}"
                                    />
                                </flux:tooltip>
                            @endcan
                        @endif
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @empty
            <flux:table.row>
                <flux:table.cell colspan="7" class="text-center text-zinc-500">
                    {{ __('general.no_banners_found') }}
                </flux:table.cell>
            </flux:table.row>
        @endforelse
    </flux:table>
</div>
