<div
    x-data="{
        activeCategory: null,
        openCategory(id) {
            this.activeCategory = id;
        },
        closeCategory() {
            this.activeCategory = null;
        }
    }"
    x-on:sidebar-categories-reset.window="closeCategory()"
    class="relative overflow-hidden"
>
    {{-- Stage 1: Root Categories --}}
    <div
        x-show="activeCategory === null"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-x-4"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-4"
    >
        <div class="px-2 pb-1.5 pt-1 flex items-center justify-between">
            <span class="text-[11px] font-bold tracking-wider text-zinc-400 dark:text-zinc-500">
                {{ __('general.categories') }}
            </span>
        </div>

        <flux:sidebar.nav>
            @foreach($this->categories as $category)
                @if($category->children->isNotEmpty())
                    <button
                        type="button"
                        wire:key="category-root-btn-{{ $category->id }}"
                        x-on:click="openCategory({{ $category->id }})"
                        class="group flex w-full items-center justify-between gap-2.5 rounded-xl px-2.5 py-2 text-start text-sm font-medium text-zinc-700 transition-all hover:bg-zinc-200/60 hover:text-zinc-950 dark:text-zinc-200 dark:hover:bg-zinc-800/80 dark:hover:text-white"
                    >
                        <span class="flex items-center gap-2.5 min-w-0">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-zinc-200/70 text-zinc-600 transition-colors group-hover:bg-teal-500/15 group-hover:text-teal-600 dark:bg-zinc-800 dark:text-zinc-300 dark:group-hover:bg-teal-400/15 dark:group-hover:text-teal-300">
                                @if($category->icon)
                                    <flux:icon :name="$category->icon" class="size-4" />
                                @else
                                    <flux:icon.cube class="size-4" />
                                @endif
                            </span>
                            <span class="truncate">{{ $category->name }}</span>
                        </span>

                        <span class="flex items-center gap-1 shrink-0 text-zinc-400 dark:text-zinc-500">
                            <span class="rounded-full bg-zinc-200/70 px-1.5 py-0.5 text-[10px] font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                {{ $category->children->count() }}
                            </span>
                            <flux:icon.chevron-left class="size-4 transition-transform duration-200 group-hover:-translate-x-0.5 group-hover:text-teal-600 dark:group-hover:text-teal-400" />
                        </span>
                    </button>
                @else
                    <a
                        href="{{ $category->url }}"
                        wire:navigate
                        wire:key="category-root-link-{{ $category->id }}"
                        class="group flex w-full items-center justify-between gap-2.5 rounded-xl px-2.5 py-2 text-start text-sm font-medium text-zinc-700 transition-all hover:bg-zinc-200/60 hover:text-zinc-950 dark:text-zinc-200 dark:hover:bg-zinc-800/80 dark:hover:text-white"
                    >
                        <span class="flex items-center gap-2.5 min-w-0">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-zinc-200/70 text-zinc-600 transition-colors group-hover:bg-teal-500/15 group-hover:text-teal-600 dark:bg-zinc-800 dark:text-zinc-300 dark:group-hover:bg-teal-400/15 dark:group-hover:text-teal-300">
                                @if($category->icon)
                                    <flux:icon :name="$category->icon" class="size-4" />
                                @else
                                    <flux:icon.cube class="size-4" />
                                @endif
                            </span>
                            <span class="truncate">{{ $category->name }}</span>
                        </span>
                    </a>
                @endif
            @endforeach
        </flux:sidebar.nav>
    </div>

    {{-- Stage 2: Subcategories Slide Panel --}}
    @foreach($this->categories as $category)
        @if($category->children->isNotEmpty())
            <div
                x-cloak
                x-show="activeCategory === {{ $category->id }}"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-x-6"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 -translate-x-6"
                wire:key="category-slide-{{ $category->id }}"
                class="space-y-2"
            >
                {{-- Slide Header / Back Button --}}
                <div class="rounded-xl border border-zinc-200/80 bg-white/80 p-2 shadow-2xs dark:border-zinc-800 dark:bg-zinc-800/60">
                    <button
                        type="button"
                        x-on:click="closeCategory()"
                        class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-xs font-semibold text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-700/60 dark:hover:text-white"
                    >
                        <flux:icon.arrow-right class="size-4 text-teal-600 dark:text-teal-400" />
                        <span>{{ __('general.back_to_categories') }}</span>
                    </button>

                    <div class="mt-1.5 flex items-center justify-between gap-2 border-t border-zinc-100 pt-2 px-2 dark:border-zinc-700/60">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-teal-500/15 text-teal-600 dark:bg-teal-400/15 dark:text-teal-300">
                                @if($category->icon)
                                    <flux:icon :name="$category->icon" class="size-4" />
                                @else
                                    <flux:icon.cube class="size-4" />
                                @endif
                            </span>
                            <span class="truncate text-sm font-bold text-zinc-900 dark:text-white">
                                {{ $category->name }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- View All in Parent Category --}}
                <a
                    href="{{ $category->url }}"
                    wire:navigate
                    class="flex items-center justify-between rounded-xl border border-teal-200/80 bg-teal-50/70 px-3 py-2 text-xs font-bold text-teal-700 transition-colors hover:bg-teal-100/80 dark:border-teal-900/60 dark:bg-teal-950/40 dark:text-teal-300 dark:hover:bg-teal-950/70"
                >
                    <span>{{ __('general.view_all') }} {{ $category->name }}</span>
                    <flux:icon.arrow-left class="size-3.5" />
                </a>

                {{-- Subcategories List --}}
                <flux:sidebar.nav>
                    @foreach($category->children as $child)
                        <a
                            href="{{ $child->url }}"
                            wire:navigate
                            wire:key="category-child-{{ $child->id }}"
                            class="group flex items-center justify-between gap-2 rounded-xl px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-200/60 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-800/80 dark:hover:text-white"
                        >
                            <span class="flex items-center gap-2 min-w-0">
                                <span class="size-1.5 shrink-0 rounded-full bg-zinc-300 transition-colors group-hover:bg-teal-500 dark:bg-zinc-600 dark:group-hover:bg-teal-400"></span>
                                <span class="truncate">{{ $child->name }}</span>
                            </span>
                            <flux:icon.chevron-left class="size-3.5 shrink-0 text-zinc-400 opacity-0 transition-all group-hover:-translate-x-0.5 group-hover:opacity-100 dark:text-zinc-500" />
                        </a>
                    @endforeach
                </flux:sidebar.nav>
            </div>
        @endif
    @endforeach
</div>
