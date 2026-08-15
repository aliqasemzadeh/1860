<flux:navbar class="lg:hidden w-full">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

    <flux:spacer />

    <flux:dropdown position="top" align="start">
        @auth
            <flux:sidebar.profile name="{{ Auth::user()->name ?: Auth::user()->mobile }}" />
        @endauth

        @guest
                <flux:profile  />
        @endguest

        <flux:menu>
            @auth
                <flux:menu.item icon="user" href="{{ route('panel.user.dashboard.index') }}" wire:navigate>{{ __('general.profile') }}</flux:menu.item>
                <flux:menu.separator />
                <flux:menu.item icon="arrow-right-start-on-rectangle" href="{{ route('logout') }}">{{ __('general.logout.title') }}</flux:menu.item>
            @endauth
        </flux:menu>
    </flux:dropdown>
</flux:navbar>
