<flux:sidebar.header>
    <flux:sidebar.brand
        href="{{ route('home') }}"
        logo="https://fluxui.dev/img/demo/logo.png"
        logo:dark="https://fluxui.dev/img/demo/dark-mode-logo.png"
        name="{{ config('app.name') }}"
    />

    <flux:sidebar.collapse class="lg:hidden" />
</flux:sidebar.header>
