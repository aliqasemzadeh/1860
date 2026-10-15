@auth
    @if(request()->routeIs('panel.*'))
        <flux:menu.item icon="shopping-bag" href="{{ route('home') }}" wire:navigate>
            {{ __('general.back_to_store') }}
        </flux:menu.item>
    @endif

    @can('user_access')
        <flux:menu.item
            icon="user"
            href="{{ route('panel.user.dashboard.index') }}"
            wire:navigate
        >
            {{ __('general.user_panel') }}
        </flux:menu.item>
    @endcan

    @can('shop_access')
        <flux:menu.item
            icon="boxes"
            href="{{ route('panel.shop.dashboard.index') }}"
            wire:navigate
        >
            {{ __('general.shop') }}
        </flux:menu.item>
    @endcan

    @can('content_access')
        <flux:menu.item
            icon="newspaper"
            href="{{ route('panel.content.post.index') }}"
            wire:navigate
        >
            {{ __('general.content') }}
        </flux:menu.item>
    @endcan

    @can('administrator_access')
        <flux:menu.item
            icon="settings"
            href="{{ route('panel.administrator.dashboard.index') }}"
            wire:navigate
        >
            {{ __('general.administrator') }}
        </flux:menu.item>
    @endcan
@endauth
