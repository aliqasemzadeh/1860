@auth
    <div class="border-t border-zinc-200/80 pt-2 dark:border-zinc-800">
        <flux:sidebar.nav>
            @if(request()->routeIs('panel.*'))
                <flux:sidebar.item
                    icon="shopping-bag"
                    href="{{ route('home') }}"
                    wire:navigate
                >
                    {{ __('general.back_to_store') }}
                </flux:sidebar.item>
            @endif

            @can('user_access')
                <flux:sidebar.item
                    icon="user"
                    href="{{ route('panel.user.dashboard.index') }}"
                    wire:navigate
                    :current="request()->routeIs('panel.user.*')"
                >
                    {{ __('general.user_panel') }}
                </flux:sidebar.item>
            @endcan

            @can('shop_access')
                <flux:sidebar.item
                    icon="boxes"
                    href="{{ route('panel.shop.dashboard.index') }}"
                    wire:navigate
                    :current="request()->routeIs('panel.shop.*')"
                >
                    {{ __('general.shop') }}
                </flux:sidebar.item>
            @endcan

            @can('content_access')
                <flux:sidebar.item
                    icon="newspaper"
                    href="{{ route('panel.content.post.index') }}"
                    wire:navigate
                    :current="request()->routeIs('panel.content.*')"
                >
                    {{ __('general.content') }}
                </flux:sidebar.item>
            @endcan

            @can('administrator_access')
                <flux:sidebar.item
                    icon="settings"
                    href="{{ route('panel.administrator.dashboard.index') }}"
                    wire:navigate
                    :current="request()->routeIs('panel.administrator.*')"
                >
                    {{ __('general.administrator') }}
                </flux:sidebar.item>
            @endcan
        </flux:sidebar.nav>
    </div>
@endauth
