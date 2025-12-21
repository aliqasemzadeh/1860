<div>
    <section class="py-8 antialiased  md:py-12">
        <div class="mx-auto max-w-7xl px-4 2xl:px-0">
            <div class="grid grid-cols-3 lg:grid-cols-8 gap-4">
                @foreach($this->categories as $category)
                    <flux:card size="sm"
                               class="hover:bg-zinc-50 dark:hover:bg-zinc-700 flex flex-col items-center justify-center gap-2">
                        <flux:icon name="{{ $category->icon }}" class="size-12"/>
                        <flux:heading
                            class="whitespace-nowrap overflow-hidden text-ellipsis">{{ $category->name }}</flux:heading>
                    </flux:card>
                @endforeach
            </div>
        </div>
    </section>
</div>
