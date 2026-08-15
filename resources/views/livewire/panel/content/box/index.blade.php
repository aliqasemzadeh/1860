<?php

use App\Models\Content\Box;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public $search = '';

    #[Computed]
    #[On('panels.administrator.content.box.index.table')]
    public function boxes()
    {
        return Box::query()
            ->when($this->search, fn($q) => $q->where('title_fa', 'like', "%{$this->search}%")->orWhere('title_en', 'like', "%{$this->search}%"))
            ->ordered()
            ->paginate(config('general.per_page'));
    }

    public function delete(int $id)
    {
        Box::query()->findOrFail($id)->delete();
        Flux::toast(__('general.deleted_successfully'));
        $this->dispatch('panels.administrator.content.box.index.table');
    }
}; ?>

<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ __('general.boxes') }}</flux:heading>

            <flux:modal.trigger name="content.box.create">
                <flux:button variant="primary" color="teal" icon="plus">
                    {{ __('general.create_box') }}
                </flux:button>
            </flux:modal.trigger>
        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable />
            </div>

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
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.administrator.content.box.edit.assign-data', { id: {{ $box->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:modal.trigger name="content.box.delete.{{ $box->id }}">
                                            <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" />
                                        </flux:modal.trigger>
                                    </flux:tooltip>
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
        </flux:card>
    </div>

    <livewire:panel.content.box.create :key="'box-create-modal'" />
    <livewire:panel.content.box.edit :key="'box-edit-modal'" />
</div>
