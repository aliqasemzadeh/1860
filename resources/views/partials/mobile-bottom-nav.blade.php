@unless(request()->routeIs('product.view'))
    <nav class="fixed inset-x-0 bottom-0 z-40 bg-white/90 dark:bg-zinc-900/90 backdrop-blur-lg border-t border-zinc-200/80 dark:border-zinc-800 lg:hidden pb-[max(0.25rem,env(safe-area-inset-bottom))] shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
        <div class="flex items-center justify-around h-16 max-w-md mx-auto px-2">
            {{-- Home --}}
            <a
                href="{{ route('home') }}"
                wire:navigate
                class="flex flex-col items-center justify-center flex-1 h-full py-1 text-[11px] transition-colors {{ request()->routeIs('home') ? 'text-teal-600 dark:text-teal-400 font-bold' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
            >
                <flux:icon name="home" class="size-5 mb-1" />
                <span>{{ __('general.home') }}</span>
            </a>

            {{-- Categories --}}
            <button
                type="button"
                x-data=""
                x-on:click="$dispatch('sidebar-categories-reset'); $dispatch('flux-sidebar-toggle')"
                class="flex flex-col items-center justify-center flex-1 h-full py-1 text-[11px] {{ request()->routeIs('category.*') ? 'text-teal-600 dark:text-teal-400 font-bold' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' }} transition-colors"
                aria-label="{{ __('general.categories') }}"
            >
                <flux:icon name="squares-2x2" class="size-5 mb-1" />
                <span>{{ __('general.categories') }}</span>
            </button>

            {{-- Search --}}
            <flux:modal.trigger name="search">
                <button
                    type="button"
                    class="flex flex-col items-center justify-center flex-1 h-full py-1 text-[11px] text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors"
                    aria-label="{{ __('general.search') }}"
                >
                    <flux:icon name="magnifying-glass" class="size-5 mb-1" />
                    <span>{{ __('general.search') }}</span>
                </button>
            </flux:modal.trigger>

            {{-- Cart --}}
            <flux:modal.trigger name="main.sidebar.basket.modal">
                <button
                    type="button"
                    class="relative flex flex-col items-center justify-center flex-1 h-full py-1 text-[11px] transition-colors {{ request()->routeIs('order.cart') ? 'text-teal-600 dark:text-teal-400 font-bold' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
                    aria-label="{{ __('general.shopping_cart') }}"
                >
                    <div class="relative">
                        <flux:icon name="shopping-cart" class="size-5 mb-1" />
                        <div
                            x-data="{ count: {{ auth()->check() ? ((int) (\Binafy\LaravelCart\Models\Cart::query()->where('user_id', auth()->id())->first()?->items()?->sum('quantity') ?? 0)) : 0 }} }"
                            x-on:cart-count-updated.window="count = $event.detail.count"
                        >
                            <template x-if="count > 0">
                                <span
                                    x-text="count > 99 ? '99+' : count"
                                    class="absolute -top-1.5 -end-2.5 flex min-w-4 h-4 px-1 items-center justify-center rounded-full bg-teal-600 text-[9px] font-black text-white ring-2 ring-white dark:ring-zinc-900 pointer-events-none"
                                ></span>
                            </template>
                        </div>
                    </div>
                    <span>{{ __('general.shopping_cart') }}</span>
                </button>
            </flux:modal.trigger>

            {{-- Profile / Account --}}
            @auth
                <a
                    href="{{ route('panel.user.dashboard.index') }}"
                    wire:navigate
                    class="flex flex-col items-center justify-center flex-1 h-full py-1 text-[11px] transition-colors {{ request()->routeIs('panel.*') ? 'text-teal-600 dark:text-teal-400 font-bold' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
                >
                    <flux:icon name="user" class="size-5 mb-1" />
                    <span>{{ __('general.profile') }}</span>
                </a>
            @endauth
            @guest
                <a
                    href="{{ route('login') }}"
                    wire:navigate
                    class="flex flex-col items-center justify-center flex-1 h-full py-1 text-[11px] transition-colors {{ request()->routeIs('login') ? 'text-teal-600 dark:text-teal-400 font-bold' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
                >
                    <flux:icon name="user" class="size-5 mb-1" />
                    <span>{{ __('general.login') }}</span>
                </a>
            @endguest
        </div>
    </nav>
@endunless
