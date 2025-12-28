<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.product_attributes') }}: {{ $product->name }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.product_attributes_description') }}</flux:subheading>
            </div>
            <div class="flex items-center gap-2">
                <flux:button href="{{ route('panel.shop.product.index') }}" wire:navigate>
                    {{ __('app.back') }}
                </flux:button>
                <flux:button variant="primary" wire:click="save">
                    {{ __('app.save') }}
                </flux:button>
            </div>
        </div>

        <flux:separator variant="subtle" />
    </div>

    @if(!$product->category)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
            <flux:text class="text-yellow-800 dark:text-yellow-200">
                {{ __('app.product_must_have_category_for_attributes') }}
            </flux:text>
        </div>
    @else
        <div class="space-y-6">
            @foreach($attributes as $groupName => $groupAttributes)
                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4">
                    <flux:heading size="md" class="mb-4">
                        {{ $groupName ?? __('app.ungrouped_attributes') }}
                    </flux:heading>
                    <div class="space-y-4">
                        @foreach($groupAttributes as $attribute)
                            <flux:field>
                                <flux:label>
                                    {{ $attribute->label }}
                                    @if($attribute->is_required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </flux:label>

                                @if($attribute->type === 'text')
                                    <flux:input
                                        wire:model="attributeValues.{{ $attribute->id }}"
                                        type="text"
                                        placeholder="{{ $attribute->meta['placeholder'] ?? '' }}"
                                    />
                                @elseif($attribute->type === 'textarea')
                                    <flux:textarea
                                        wire:model="attributeValues.{{ $attribute->id }}"
                                        placeholder="{{ $attribute->meta['placeholder'] ?? '' }}"
                                        rows="3"
                                    />
                                @elseif($attribute->type === 'number')
                                    <flux:input
                                        wire:model="attributeValues.{{ $attribute->id }}"
                                        type="number"
                                        step="{{ $attribute->meta['step'] ?? '1' }}"
                                        min="{{ $attribute->meta['min'] ?? '' }}"
                                        max="{{ $attribute->meta['max'] ?? '' }}"
                                    />
                                @elseif($attribute->type === 'boolean')
                                    <flux:checkbox
                                        wire:model="attributeValues.{{ $attribute->id }}"
                                    />
                                @elseif($attribute->type === 'date')
                                    <flux:input
                                        wire:model="attributeValues.{{ $attribute->id }}"
                                        type="date"
                                    />
                                @elseif($attribute->type === 'select')
                                    <flux:select
                                        variant="listbox"
                                        wire:model="attributeValues.{{ $attribute->id }}"
                                        placeholder="{{ __('app.select_option') }}"
                                    >
                                        @foreach($attribute->options as $option)
                                            <flux:select.option value="{{ $option->value }}">
                                                {{ $option->label }}
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>
                                @elseif($attribute->type === 'multiselect')
                                    <div class="space-y-2">
                                        @foreach($attribute->options as $option)
                                            <div class="flex items-center gap-2">
                                                <flux:checkbox
                                                    wire:model="attributeValues.{{ $attribute->id }}"
                                                    value="{{ $option->value }}"
                                                />
                                                <flux:label>{{ $option->label }}</flux:label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if($attribute->meta['help'] ?? null)
                                    <flux:text class="mt-1 text-xs text-gray-500">
                                        {{ $attribute->meta['help'] }}
                                    </flux:text>
                                @endif
                            </flux:field>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if($attributes->isEmpty())
                <div class="text-center py-12 text-zinc-500">
                    {{ __('app.no_attributes_assigned_to_category') }}
                </div>
            @endif
        </div>
    @endif
</div>
