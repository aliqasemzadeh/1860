<div>
    <flux:sidebar.nav>
        @foreach($this->categories as $category)
            @if($category->children->isNotEmpty())
                <flux:sidebar.group
                    expandable
                    :expanded="false"
                    :icon="$category->icon"
                    :heading="$category->name"
                    wire:key="category-group-{{ $category->id }}"
                >
                    <flux:sidebar.item
                        :href="$category->url"
                        wire:navigate
                        class="text-teal-600 dark:text-teal-400 font-semibold"
                    >
                        {{ __('general.view_all') }} {{ $category->name }}
                    </flux:sidebar.item>

                    @foreach ($category->children as $child)
                        <flux:sidebar.item 
                            wire:key="category-child-{{ $child->id }}"
                            :href="$child->url"
                            wire:navigate
                        >
                            {{ $child->name }}
                        </flux:sidebar.item>
                    @endforeach
                </flux:sidebar.group>
            @else
                <flux:sidebar.item 
                    :icon="$category->icon" 
                    :href="$category->url"
                    wire:navigate
                    wire:key="category-item-{{ $category->id }}"
                >
                    {{ $category->name }}
                </flux:sidebar.item>
            @endif
        @endforeach
    </flux:sidebar.nav>
</div>
