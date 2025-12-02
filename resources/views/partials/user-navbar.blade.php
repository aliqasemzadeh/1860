<flux:navbar class="lg:hidden w-full">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

    <flux:spacer />

    <flux:dropdown position="top" align="start">
        <flux:profile avatar="https://fluxui.dev/img/demo/user.png" />

        <flux:menu>
            <flux:menu.radio.group>
                <flux:menu.radio checked>Olivia Martin</flux:menu.radio>
                <flux:menu.radio>Truly Delta</flux:menu.radio>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <flux:menu.item icon="arrow-right-start-on-rectangle" href="{{ route('logout') }}">{{ __('app.logout.title') }}</flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</flux:navbar>
