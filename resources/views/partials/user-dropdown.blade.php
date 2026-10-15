@auth
    <flux:dropdown position="top" align="start" class="w-full">
        <flux:sidebar.profile name="{{ Auth::user()->name ?: Auth::user()->mobile }}" class="w-full" />

        <flux:menu class="min-w-52">
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
        </flux:menu>
    </flux:dropdown>
@endauth
