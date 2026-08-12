@php($general = app(\App\Settings\GeneralSettings::class))
<flux:sidebar.header>
    @if ($general->logo_path)
        <flux:sidebar.brand
            href="{{ route('home') }}"
            name="{{ $general->title }}"
            logo="{{ $general->logoUrl() }}"
            logo:dark="{{ $general->logoUrl() }}"
        />
    @else
        <flux:sidebar.brand
            href="{{ route('home') }}"
            name="{{ $general->title }}"
        />
    @endif

    <flux:sidebar.collapse class="lg:hidden" />
</flux:sidebar.header>
