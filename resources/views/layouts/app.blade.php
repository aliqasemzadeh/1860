<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" dir="rtl">
<head>
    @include('partials.head')
    @isset($head)
        {{ $head }}
    @else
        <x-seo :title="$title ?? null" />
    @endisset

    @if(isset($product))
        <x-product-schema :product="$product" />
    @endif
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
<flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-e border-zinc-200 dark:border-zinc-700">
    @include('partials.sidebar-header')

    <div class="flex-1 overflow-y-auto py-1 [scrollbar-width:thin]">
        <livewire:main.sidebar.categories />
    </div>

    <div class="mt-auto border-t border-zinc-200/80 pt-2 space-y-2 dark:border-zinc-800">
        @include('partials.user-dropdown')
        @include('partials.theme-icon')
    </div>
</flux:sidebar>

<flux:header sticky class="bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
    <flux:navbar class="w-full flex flex-row items-center gap-2">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-3" inset="left"/>

        <div class="flex-1 max-w-xl">
            <livewire:main.header.search/>
        </div>

        <flux:spacer class="hidden sm:block"/>

        <livewire:main.sidebar.basket/>

        <div class="hidden sm:flex items-center">
            @auth
                <flux:dropdown position="bottom" align="end">
                    <flux:button icon="user" square variant="ghost" />

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

            @guest
                <flux:button icon="user" href="{{ route('login') }}" square variant="ghost" wire:navigate />
            @endguest
        </div>
    </flux:navbar>
</flux:header>

<flux:main class="p-2 md:p-6 pb-20 lg:pb-6">
    {{ $slot }}
</flux:main>

@include('partials.mobile-bottom-nav')
@include('partials.foot')
</body>
</html>
