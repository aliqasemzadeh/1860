@php($general = app(\App\Settings\GeneralSettings::class))
<flux:navbar class="lg:hidden w-full flex items-center justify-between gap-2 py-1">
    <div class="flex items-center gap-2 min-w-0">
        <flux:sidebar.toggle class="lg:hidden shrink-0" icon="bars-3" inset="left" />

        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2 min-w-0">
            @if ($general->logo_path)
                <img src="{{ $general->logoUrl() }}" alt="{{ $general->title }}" class="h-7 w-auto shrink-0 object-contain" />
            @endif
            <span class="truncate text-sm font-bold text-zinc-800 dark:text-zinc-100">
                {{ $general->title }}
            </span>
        </a>
    </div>

    <div class="flex items-center gap-1 shrink-0">
        <flux:tooltip content="{{ __('general.home') }}">
            <flux:button
                size="sm"
                variant="ghost"
                icon="shopping-bag"
                href="{{ route('home') }}"
                wire:navigate
            />
        </flux:tooltip>

        <flux:dropdown position="bottom" align="end">
            @auth
                <flux:profile name="{{ Auth::user()->name ?: Auth::user()->mobile }}" />
            @endauth

            @guest
                <flux:profile />
            @endguest

            <flux:menu>
                @auth
                    <flux:menu.item icon="user" href="{{ route('panel.user.dashboard.index') }}" wire:navigate>
                        {{ __('general.profile') }}
                    </flux:menu.item>
                    <flux:menu.item icon="shopping-bag" href="{{ route('home') }}" wire:navigate>
                        {{ __('general.back_to_store') }}
                    </flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item icon="arrow-right-start-on-rectangle" href="{{ route('logout') }}">
                        {{ __('general.logout_title') }}
                    </flux:menu.item>
                @endauth
            </flux:menu>
        </flux:dropdown>
    </div>
</flux:navbar>
