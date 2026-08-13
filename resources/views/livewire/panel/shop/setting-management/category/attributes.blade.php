<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.category_attributes') }}: {{ $category->name }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.category_attributes_description') }}</flux:subheading>
            </div>
            <div class="flex items-center gap-2">
                <flux:button href="{{ route('panel.shop.setting-management.category.index') }}" wire:navigate>
                    {{ __('app.back') }}
                </flux:button>
                <flux:button variant="primary" wire:click="save">
                    {{ __('general.save') }}
                </flux:button>
            </div>
        </div>

        <flux:separator variant="subtle" />
    </div>

    <div class="space-y-6">
        @foreach($allAttributes as $groupName => $attributes)
            <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4">
                <flux:heading size="md" class="mb-4">
                    {{ $groupName ?? __('app.ungrouped_attributes') }}
                </flux:heading>
                <div class="space-y-2">
                    @foreach($attributes as $attribute)
                        <div class="flex items-center gap-3 p-3 bg-white dark:bg-zinc-800 rounded border border-zinc-200 dark:border-zinc-700">
                            <flux:checkbox
                                wire:model="selectedAttributes"
                                value="{{ $attribute->id }}"
                            />
                            <div class="flex-1">
                                <div class="font-medium">{{ $attribute->label }}</div>
                                <div class="text-sm text-zinc-500">
                                    <code>{{ $attribute->key }}</code>
                                    <span class="mx-2">•</span>
                                    {{ __('app.attribute_type_' . $attribute->type) }}
                                    @if($attribute->is_required)
                                        <span class="text-red-500">• {{ __('app.required') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        @if($allAttributes->isEmpty())
            <div class="text-center py-12 text-zinc-500">
                {{ __('app.no_attributes_found') }}
            </div>
        @endif
    </div>
</div>














