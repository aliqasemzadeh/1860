<flux:dropdown position="top" align="start" class="max-lg:hidden">
    @auth
    <flux:sidebar.profile name="{{ Auth::user()->name ?: Auth::user()->mobile }}" />
    @endauth

    <flux:menu>
        <flux:menu.item icon="user" href="{{ route('panel.user.dashboard.index') }}" wire:navigate>{{ __('general.profile') }}</flux:menu.item>
        <flux:menu.separator />
        <flux:menu.item icon="arrow-right-start-on-rectangle" href="{{ route('logout') }}">{{ __('general.logout.title') }}</flux:menu.item>
    </flux:menu>
</flux:dropdown>
