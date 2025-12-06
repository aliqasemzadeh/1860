<flux:modal name="shop.category.edit.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.edit_category') }} : {{ isset($name) ? $name : '' }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.edit_category_description') }}</flux:text>
        </div>
        <form wire:submit="edit" method="post">
            <div class="pb-2 space-y-3">
                <flux:field>
                    <flux:label>{{ __('app.name') }}</flux:label>
                    <flux:input wire:model="name" type="text" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.slug') }}</flux:label>
                    <flux:input wire:model="slug" type="text" />
                    <flux:error name="slug" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.slug_fa') }}</flux:label>
                    <flux:input wire:model="slug_fa" type="text" />
                    <flux:error name="slug_fa" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.icon') }}</flux:label>
                    <flux:input wire:model="icon" type="text" />
                    <flux:error name="icon" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.parent_category') }}</flux:label>
                    <flux:select variant="listbox" placeholder="{{ __('app.root_category') }}" wire:model="main_category_id">
                        <flux:select.option value="0">
                            <div class="flex items-center gap-2">
                                <flux:icon.shield-check variant="mini" class="text-zinc-400" />
                                {{ __('app.root_category') }}
                            </div>
                        </flux:select.option>

                        @foreach($roots as $root)
                            <flux:select.option value="{{ $root->id }}" wire:key="root-{{ $root->id }}">
                                <div class="flex items-center gap-2">
                                    {{ $root->name }}
                                </div>
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:text class="mt-1 text-xs text-gray-500">{{ __('app.parent_help_one_level') }}</flux:text>
                    <flux:error name="main_category_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.sort_order') }}</flux:label>
                    <flux:input wire:model="sort_order" type="number" min="1" />
                    <flux:error name="sort_order" />
                </flux:field>
            </div>
            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('app.update') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
