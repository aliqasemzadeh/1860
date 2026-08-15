<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.posts') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.posts_description') }}</flux:subheading>
            </div>
            @can('content_post_create')
                <flux:modal.trigger name="panel.content.post.create.modal">
                    <flux:button variant="primary" color="teal" icon="plus">{{ __('general.create_post') }}</flux:button>
                </flux:modal.trigger>
            @endcan
        </div>

        <flux:separator variant="subtle" />
    </div>

    <flux:card class="mb-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('general.search') }}</flux:label>
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    clearable
                    type="text"
                    placeholder="{{ __('general.search_in_posts') }}"
                />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('general.status') }}</flux:label>
                <flux:select wire:model.live="statusFilter" searchable placeholder="{{ __('general.status') }}">
                    <flux:select.option value="">{{ __('general.all_statuses') }}</flux:select.option>
                    <flux:select.option value="draft">{{ __('general.post_status_draft') }}</flux:select.option>
                    <flux:select.option value="published">{{ __('general.post_status_published') }}</flux:select.option>
                    <flux:select.option value="archived">{{ __('general.post_status_archived') }}</flux:select.option>
                </flux:select>
            </flux:field>
        </div>
    </flux:card>

    <livewire:panel.content.post.create />
    <livewire:panel.content.post.edit />

    <flux:table :paginate="$this->posts">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'title'" :direction="$sortDirection" wire:click="sort('title')">
                {{ __('general.title') }}
            </flux:table.column>
            <flux:table.column>{{ __('general.status') }}</flux:table.column>
            <flux:table.column>{{ __('general.tags') }}</flux:table.column>
            <flux:table.column>{{ __('general.related_products') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'published_at'" :direction="$sortDirection" wire:click="sort('published_at')">
                {{ __('general.date') }}
            </flux:table.column>
            <flux:table.column>{{ __('general.options') }}</flux:table.column>
        </flux:table.columns>

        @forelse ($this->posts as $post)
            <flux:table.row :key="$post->id">
                <flux:table.cell>
                    <div class="flex items-center gap-3">
                        @if ($post->featured_image_url)
                            <flux:avatar src="{{ $post->featured_image_url }}" />
                        @endif
                        <div>
                            <div class="font-medium">{{ $post->title }}</div>
                            <flux:text class="text-xs text-zinc-500">{{ $post->slug }}</flux:text>
                        </div>
                    </div>
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    @if ($post->status === 'published')
                        <flux:badge color="green" size="sm">{{ __('general.post_status_published') }}</flux:badge>
                    @elseif ($post->status === 'archived')
                        <flux:badge color="zinc" size="sm">{{ __('general.post_status_archived') }}</flux:badge>
                    @else
                        <flux:badge color="amber" size="sm">{{ __('general.post_status_draft') }}</flux:badge>
                    @endif
                </flux:table.cell>
                <flux:table.cell>
                    <div class="flex flex-wrap gap-1">
                        @forelse ($post->tags as $tag)
                            <flux:badge size="sm" color="zinc">{{ $tag->name }}</flux:badge>
                        @empty
                            <span class="text-zinc-400">-</span>
                        @endforelse
                    </div>
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $post->products_count }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ jalali($post->published_at ?? $post->created_at) }}
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        @can('content_post_edit')
                            <flux:tooltip content="{{ __('general.edit') }}">
                                <flux:button
                                    size="xs"
                                    variant="primary"
                                    color="blue"
                                    icon="pencil"
                                    icon:variant="outline"
                                    wire:click="$dispatch('panel.content.post.edit.assign-data', { id: {{ $post->id }} })"
                                />
                            </flux:tooltip>
                        @endcan

                        @can('content_post_delete')
                            <flux:tooltip content="{{ __('general.delete') }}">
                                <flux:button
                                    size="xs"
                                    variant="primary"
                                    color="red"
                                    icon="trash"
                                    icon:variant="outline"
                                    wire:click="delete({{ $post->id }})"
                                    wire:confirm="{{ __('general.are_you_sure') }}"
                                />
                            </flux:tooltip>
                        @endcan
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @empty
            <flux:table.row>
                <flux:table.cell colspan="6">
                    {{ __('general.no_posts_found') }}
                </flux:table.cell>
            </flux:table.row>
        @endforelse
    </flux:table>
</div>
