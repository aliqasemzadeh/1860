<div>
    <flux:sidebar.nav>
        @foreach($this->categories as $category)
                <flux:modal.trigger name="{{ $category->slug }}" shortcut="cmd.k">
                    <flux:sidebar.item :icon="$category->icon" href="#">{{ $category->name }}</flux:sidebar.item>
                </flux:modal.trigger>

                    <flux:modal name="{{ $category->slug }}" variant="bare" class="w-full max-w-[30rem] my-[12vh] max-h-screen overflow-y-hidden">
                        <flux:command wire:key="command-{{ $category->slug }}" class="border-none shadow-lg inline-flex flex-col max-h-[76vh]">
                            <flux:command.input placeholder="{{ __('app.search') }}" closable />
                            <flux:command.items>
                                @foreach ($category->children as $child)
                                        <flux:command.item wire:key="command-{{ $category->slug }}:item-{{ $child->slug }}">{{ $child->name }}</flux:command.item>
                                @endforeach
                            </flux:command.items>
                        </flux:command>
                    </flux:modal>
        @endforeach
    </flux:sidebar.nav>

</div>
