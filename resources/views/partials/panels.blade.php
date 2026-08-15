<flux:sidebar.nav>
    @can('user_access')
        <flux:sidebar.item icon="user" href="{{ route('panel.user.dashboard.index') }}">{{ __('app.user_panel') }}</flux:sidebar.item>
    @endcan
    @can('shop_access')
    <flux:sidebar.item icon="boxes" href="{{ route('panel.shop.dashboard.index') }}">{{ __('app.shop') }}</flux:sidebar.item>
    @endcan
    @can('content_access')
        <flux:sidebar.item icon="newspaper" href="{{ route('panel.content.post.index') }}">{{ __('app.content') }}</flux:sidebar.item>
    @endcan
    @can('administrator_access')
    <flux:sidebar.item icon="settings" href="{{ route('panel.administrator.dashboard.index') }}">{{ __('app.administrator') }}</flux:sidebar.item>
    @endcan
</flux:sidebar.nav>
