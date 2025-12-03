<div>
    <flux:sidebar.nav>
        @foreach($this->categories as $category)
            <flux:modal.trigger name="{{ $category->slug }}" shortcut="cmd.k">
                <flux:sidebar.item :icon="$category->icon" href="#">{{ $category->name }}</flux:sidebar.item>
            </flux:modal.trigger>
        @endforeach
    </flux:sidebar.nav>

    @foreach($this->categories as $category)
    <flux:modal name="{{ $category->slug }}" variant="bare" class="w-full max-w-[30rem] my-[12vh] max-h-screen overflow-y-hidden">
        <flux:command class="border-none shadow-lg inline-flex flex-col max-h-[76vh]">
            <flux:command.input placeholder="{{ __('app.search') }}" closable />
            @foreach ($category->children as $child)
            <flux:command.items>
                <flux:command.item>{{ $child->name }}</flux:command.item>
            </flux:command.items>
            @endforeach
        </flux:command>
    </flux:modal>
    @endforeach
</div>
