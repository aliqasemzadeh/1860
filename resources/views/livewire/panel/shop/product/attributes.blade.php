<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between gap-3">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.product_attributes') }}: {{ $product->name }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.product_attributes_description') }}</flux:subheading>
            </div>
            <div class="flex items-center gap-2">
                <flux:button href="{{ route('panel.shop.product.index') }}" wire:navigate>
                    {{ __('general.back') }}
                </flux:button>
                @if($product->category)
                    <flux:tooltip content="{{ __('general.catalog_ai_specs_wizard_tooltip') }}">
                        <flux:button
                            type="button"
                            variant="primary"
                            color="violet"
                            icon="wand-sparkles"
                            icon:variant="outline"
                            wire:click="openAiWizard"
                        >
                            {{ __('general.catalog_ai_specs_wizard') }}
                        </flux:button>
                    </flux:tooltip>
                @endif
                <flux:button variant="primary" color="orange" wire:click="save">
                    {{ __('general.save') }}
                </flux:button>
            </div>
        </div>

        <flux:separator variant="subtle" />
    </div>

    @if(!$product->category)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
            <flux:text class="text-yellow-800 dark:text-yellow-200">
                {{ __('general.product_must_have_category_for_attributes') }}
            </flux:text>
        </div>
    @else
        <div class="space-y-6">
            @foreach($attributes as $groupName => $groupAttributes)
                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4">
                    <flux:heading size="md" class="mb-4">
                        {{ $groupName ?? __('general.ungrouped_attributes') }}
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
                                        placeholder="1403/01/01"
                                        dir="ltr"
                                    />
                                @elseif($attribute->type === 'select')
                                    <flux:select
                                        variant="listbox"
                                        wire:model="attributeValues.{{ $attribute->id }}"
                                        placeholder="{{ __('general.select_option') }}"
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
                <div class="text-center py-12 text-zinc-500 space-y-3">
                    <div>{{ __('general.no_attributes_assigned_to_category') }}</div>
                    <flux:button
                        type="button"
                        variant="primary"
                        color="violet"
                        icon="wand-sparkles"
                        wire:click="openAiWizard"
                    >
                        {{ __('general.catalog_ai_build_specs') }}
                    </flux:button>
                </div>
            @endif
        </div>
    @endif

    <flux:modal name="panel.shop.product.attributes.ai-wizard.modal" class="md:w-2/3 lg:w-1/2" flyout position="right">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('general.catalog_ai_specs_wizard') }}</flux:heading>
                <flux:text class="mt-2">{{ __('general.catalog_ai_specs_wizard_hint') }}</flux:text>
            </div>

            <div class="flex items-center gap-2 text-sm">
                <span @class([
                    'rounded-full px-3 py-1',
                    'bg-violet-600 text-white' => $aiStep === 1,
                    'bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200' => $aiStep !== 1,
                ])>1. {{ __('general.catalog_ai_step_schema') }}</span>
                <span @class([
                    'rounded-full px-3 py-1',
                    'bg-violet-600 text-white' => $aiStep === 2,
                    'bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200' => $aiStep !== 2,
                ])>2. {{ __('general.catalog_ai_step_values') }}</span>
            </div>

            @if($aiStep === 1)
                <div class="space-y-4">
                    <flux:field>
                        <flux:label>{{ __('general.catalog_ai_product_kind') }}</flux:label>
                        <flux:input
                            wire:model="productKind"
                            placeholder="{{ __('general.catalog_ai_product_kind_placeholder') }}"
                        />
                        <flux:text class="mt-1 text-xs text-zinc-500">{{ __('general.catalog_ai_product_kind_help') }}</flux:text>
                    </flux:field>

                    <div class="flex flex-wrap gap-2">
                        <flux:button
                            type="button"
                            variant="primary"
                            color="violet"
                            icon="wand-sparkles"
                            wire:click="suggestAttributeSchema"
                            wire:loading.attr="disabled"
                            wire:target="suggestAttributeSchema"
                        >
                            <span wire:loading.remove wire:target="suggestAttributeSchema">{{ __('general.catalog_ai_suggest_schema') }}</span>
                            <span wire:loading wire:target="suggestAttributeSchema">{{ __('general.catalog_ai_generating') }}</span>
                        </flux:button>

                        @if($product->category?->attributes?->isNotEmpty())
                            <flux:button type="button" variant="ghost" wire:click="skipToValueStep">
                                {{ __('general.catalog_ai_skip_to_values') }}
                            </flux:button>
                        @endif
                    </div>

                    @if($schemaPreview !== [])
                        <div class="space-y-4 max-h-[28rem] overflow-y-auto pe-1">
                            @foreach($schemaPreview as $groupIndex => $group)
                                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-3 space-y-3">
                                    <flux:heading size="sm">{{ $group['name'] }}</flux:heading>
                                    <div class="space-y-2">
                                        @foreach($group['attributes'] as $attributeIndex => $attribute)
                                            <div class="flex items-start justify-between gap-3 rounded-md bg-zinc-50 dark:bg-zinc-900 px-3 py-2">
                                                <div class="min-w-0">
                                                    <div class="font-medium truncate">{{ $attribute['label'] }}</div>
                                                    <div class="text-xs text-zinc-500" dir="ltr">
                                                        {{ $attribute['key'] }} · {{ $attribute['type'] }}
                                                        @if($attribute['is_required']) · {{ __('general.required') }} @endif
                                                    </div>
                                                    @if(!empty($attribute['options']))
                                                        <div class="mt-1 text-xs text-zinc-500">
                                                            {{ collect($attribute['options'])->pluck('label')->implode('، ') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <flux:button
                                                    type="button"
                                                    size="xs"
                                                    variant="ghost"
                                                    icon="trash"
                                                    wire:click="removeSchemaAttribute({{ $groupIndex }}, {{ $attributeIndex }})"
                                                />
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <flux:button
                            type="button"
                            class="w-full"
                            variant="primary"
                            color="orange"
                            wire:click="applyAttributeSchema"
                            wire:loading.attr="disabled"
                            wire:target="applyAttributeSchema"
                        >
                            <span wire:loading.remove wire:target="applyAttributeSchema">{{ __('general.catalog_ai_apply_schema') }}</span>
                            <span wire:loading wire:target="applyAttributeSchema">{{ __('general.saving') }}</span>
                        </flux:button>
                    @endif
                </div>
            @else
                <div class="space-y-4">
                    <flux:field>
                        <flux:label>{{ __('general.catalog_ai_model_hint') }}</flux:label>
                        <flux:input
                            wire:model="modelHint"
                            placeholder="{{ __('general.catalog_ai_model_hint_placeholder') }}"
                        />
                        <flux:text class="mt-1 text-xs text-zinc-500">{{ __('general.catalog_ai_model_hint_help') }}</flux:text>
                    </flux:field>

                    <div class="flex flex-wrap gap-2">
                        <flux:button type="button" variant="ghost" wire:click="$set('aiStep', 1)">
                            {{ __('general.back') }}
                        </flux:button>
                        <flux:button
                            type="button"
                            class="flex-1"
                            variant="primary"
                            color="violet"
                            icon="wand-sparkles"
                            wire:click="generateAttributeValues"
                            wire:loading.attr="disabled"
                            wire:target="generateAttributeValues"
                        >
                            <span wire:loading.remove wire:target="generateAttributeValues">{{ __('general.catalog_ai_fill_values') }}</span>
                            <span wire:loading wire:target="generateAttributeValues">{{ __('general.catalog_ai_generating') }}</span>
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </flux:modal>
</div>
