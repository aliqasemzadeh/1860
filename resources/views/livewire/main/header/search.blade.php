<div>
    <flux:modal.trigger name="search" shortcut="cmd.f">
        <flux:input as="button" placeholder="{{ __('app.search') }}" icon="magnifying-glass" kbd="⌘K" />
    </flux:modal.trigger>
    <flux:modal name="search" variant="bare" class="w-full max-w-[30rem] my-[12vh] max-h-screen overflow-y-hidden">
        <flux:command class="border-none shadow-lg inline-flex flex-col max-h-[76vh]">
            <flux:command.input placeholder="Search..." closable />
            <flux:command.items>
                <flux:command.item icon="user-plus" kbd="⌘A">
                    مانیتور
                </flux:command.item>
                <flux:command.item icon="document-plus">کیس</flux:command.item>
                <flux:command.item icon="folder-plus" kbd="⌘⇧N">مودم</flux:command.item>
                <flux:command.item icon="book-open">تست</flux:command.item>
                <flux:command.item icon="newspaper">تست </flux:command.item>
                <flux:command.item icon="cog-6-tooth" kbd="⌘,">تست</flux:command.item>
            </flux:command.items>
        </flux:command>
    </flux:modal>
</div>
