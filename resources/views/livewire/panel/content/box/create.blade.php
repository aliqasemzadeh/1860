<flux:modal name="content.box.create" flyout position="right" class="w-full max-w-lg">
    <form wire:submit="save" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.create_box') }}</flux:heading>
            <flux:text>{{ __('general.create_box_description') }}</flux:text>
        </div>

        <flux:input wire:model="form.title_fa" label="{{ __('general.title_fa') }}" placeholder="{{ __('general.title_fa') }}" />
        <flux:input wire:model="form.title_en" label="{{ __('general.title_en') }}" placeholder="{{ __('general.title_en') }}" />

        <div class="grid grid-cols-3 gap-4">
            <flux:input type="color" wire:model="form.color_theme.bg" label="{{ __('general.bg_color') }}" />
            <flux:input type="color" wire:model="form.color_theme.text" label="{{ __('general.text_color') }}" />
            <flux:input type="color" wire:model="form.color_theme.accent" label="{{ __('general.accent_color') }}" />
        </div>

        <flux:pillbox wire:model="form.product_ids" label="{{ __('general.products') }}" searchable>
            @foreach($this->products as $product)
                <flux:pillbox.item :value="$product->id">{{ $product->name }}</flux:pillbox.item>
            @endforeach
        </flux:pillbox>

        <flux:file-upload wire:model="form.image" label="{{ __('general.image') }}" variant="inline" />

        <flux:field variant="inline">
            <flux:label>{{ __('general.active') }}</flux:label>
            <flux:switch wire:model="form.is_active" />
        </flux:field>

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('general.save') }}
        </flux:button>
    </form>
</flux:modal>
