<flux:sidebar.nav>
    @can('user_access')
        <flux:sidebar.item icon="user" href="{{ route('panel.user.dashboard.index') }}">{{ __('general.user_panel') }}</flux:sidebar.item>
    @endcan
    @can('shop_access')
    <flux:sidebar.item icon="boxes" href="{{ route('panel.shop.dashboard.index') }}">{{ __('general.shop') }}</flux:sidebar.item>
    @endcan
    @can('content_access')
        <flux:sidebar.item icon="newspaper" href="{{ route('panel.content.post.index') }}">{{ __('general.content') }}</flux:sidebar.item>
    @endcan
    @can('administrator_access')
    <flux:sidebar.item icon="settings" href="{{ route('panel.administrator.dashboard.index') }}">{{ __('general.administrator') }}</flux:sidebar.item>
    @endcan
</flux:sidebar.nav>
