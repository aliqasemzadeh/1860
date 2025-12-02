<flux:sidebar.nav>
    <flux:sidebar.item icon="cpu" href="{{ route('service-center.dashboard.index') }}">{{ __('app.service_center') }}</flux:sidebar.item>
    <flux:sidebar.item icon="boxes" href="{{ route('shop.dashboard.index') }}">{{ __('app.shop') }}</flux:sidebar.item>
    <flux:sidebar.item icon="crm" href="{{ route('crm.dashboard.index') }}">{{ __('app.crm') }}</flux:sidebar.item>
    <flux:sidebar.item icon="settings" href="{{ route('administrator.dashboard.index') }}">{{ __('app.administrator') }}</flux:sidebar.item>
</flux:sidebar.nav>
