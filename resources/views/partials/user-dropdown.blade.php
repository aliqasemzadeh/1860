<flux:dropdown position="top" align="start" class="max-lg:hidden">
    <flux:sidebar.profile avatar="https://fluxui.dev/img/demo/user.png" name="Olivia Martin" />

    <flux:menu>
        <flux:menu.radio.group>
            <flux:menu.radio checked>Olivia Martin</flux:menu.radio>
            <flux:menu.radio>Truly Delta</flux:menu.radio>
        </flux:menu.radio.group>

        <flux:menu.separator />

        <flux:menu.item icon="arrow-right-start-on-rectangle" href="{{ route('logout') }}">{{ __('app.logout.title') }}</flux:menu.item>
    </flux:menu>
</flux:dropdown>
