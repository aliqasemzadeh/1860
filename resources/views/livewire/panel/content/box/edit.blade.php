<flux:modal name="content.box.edit" flyout position="right" class="w-full max-w-lg">
    <form wire:submit="save" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.edit_box') }} {{ $form->title_fa }}</flux:heading>
            <flux:text>{{ __('general.edit_box_description') }}</flux:text>
        </div>

        <flux:input wire:model="form.title_fa" label="{{ __('general.title_fa') }}" placeholder="{{ __('general.title_fa') }}" />
        <flux:input wire:model="form.title_en" label="{{ __('general.title_en') }}" placeholder="{{ __('general.title_en') }}" />

        <div class="grid grid-cols-3 gap-4">
            <flux:input type="color" wire:model="form.color_theme.bg" label="{{ __('general.bg_color') }}" />
            <flux:input type="color" wire:model="form.color_theme.text" label="{{ __('general.text_color') }}" />
            <flux:input type="color" wire:model="form.color_theme.accent" label="{{ __('general.accent_color') }}" />
        </div>

        <flux:file-upload wire:model="form.image" label="{{ __('general.logo') }}" variant="inline" />

        @if ($form->box?->getFirstMediaUrl('box_images'))
            <div class="flex items-center gap-3 rounded-xl border border-zinc-200 dark:border-zinc-700 p-3">
                <img
                    src="{{ $form->box->getFirstMediaUrl('box_images') }}"
                    alt="{{ $form->title_fa }}"
                    class="h-12 w-12 object-contain rounded-lg bg-white"
                />
                <flux:text>{{ __('general.logo') }}</flux:text>
            </div>
        @endif

        <flux:field variant="inline">
            <flux:label>{{ __('general.active') }}</flux:label>
            <flux:switch wire:model="form.is_active" />
        </flux:field>

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('general.save') }}
        </flux:button>
    </form>
</flux:modal>
