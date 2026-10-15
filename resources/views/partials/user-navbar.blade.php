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
        <flux:dropdown position="bottom" align="end">
            @auth
                <flux:profile name="{{ Auth::user()->name ?: Auth::user()->mobile }}" />
            @endauth

            @guest
                <flux:profile />
            @endguest

            <flux:menu class="min-w-52">
                @auth
                    <div class="px-2 py-1.5">
                        <div class="truncate text-xs font-bold text-zinc-900 dark:text-zinc-100">
                            {{ Auth::user()->name ?: Auth::user()->mobile }}
                        </div>
                        @if(Auth::user()->name && Auth::user()->mobile)
                            <div class="truncate text-[11px] text-zinc-500 dark:text-zinc-400" dir="ltr">
                                {{ Auth::user()->mobile }}
                            </div>
                        @endif
                    </div>

                    <flux:menu.separator />

                    @include('partials.panels')

                    <flux:menu.separator />

                    <flux:menu.item icon="arrow-right-start-on-rectangle" href="{{ route('logout') }}">
                        {{ __('general.logout_title') }}
                    </flux:menu.item>
                @endauth
            </flux:menu>
        </flux:dropdown>
    </div>
</flux:navbar>
