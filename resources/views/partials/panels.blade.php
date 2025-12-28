<flux:sidebar.nav>
    @can('service_center_access')
    <flux:sidebar.item icon="cpu" href="{{ route('panel.service-center.dashboard.index') }}">{{ __('app.service_center') }}</flux:sidebar.item>
    @endcan
    @can('shop_access')
    <flux:sidebar.item icon="boxes" href="{{ route('panel.shop.dashboard.index') }}">{{ __('app.shop') }}</flux:sidebar.item>
    @endcan
    @can('crm_access')
    <flux:sidebar.item icon="crm" href="{{ route('panel.crm.dashboard.index') }}">{{ __('app.crm') }}</flux:sidebar.item>
    @endcan
        @can('accounting_access')
            <flux:sidebar.item icon="chart-no-axes-combined" href="{{ route('accounting.dashboard.index') }}">{{ __('app.accounting') }}</flux:sidebar.item>
        @endcan
    @can('administrator_access')
    <flux:sidebar.item icon="settings" href="{{ route('panel.administrator.dashboard.index') }}">{{ __('app.administrator') }}</flux:sidebar.item>
    @endcan
</flux:sidebar.nav>
