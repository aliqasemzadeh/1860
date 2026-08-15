<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" dir="rtl">
<head>
    @include('partials.head')
    @isset($head)
        {{ $head }}
    @else
        <x-seo :title="$title ?? null" />
    @endisset
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
<flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    @include('partials.sidebar-header')

    <livewire:main.sidebar.categories />

    <flux:sidebar.spacer />

    <flux:sidebar.nav>
            <flux:sidebar.item icon="newspaper" href="{{ route('post.index') }}" wire:navigate :current="request()->routeIs('post.*')">{{ __('app.blog') }}</flux:sidebar.item>
            <flux:sidebar.item icon="mail" href="{{ route('contact.index') }}">{{ __('general.contact_us') }}</flux:sidebar.item>
    </flux:sidebar.nav>


    @include('partials.panels')
    @include('partials.user-dropdown')
    @include('partials.theme-icon')

</flux:sidebar>

<flux:header sticky class="bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
    <flux:navbar class="w-full flex flex-row">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-3" inset="left"/>

        <livewire:main.header.search/>

        <flux:spacer/>

        <livewire:main.sidebar.basket/>

        @auth
            <flux:button icon="user" href="{{ route('panel.user.dashboard.index') }}" square variant="ghost"/>
        @endauth

        @guest
            <flux:button icon="user" href="{{ route('login') }}" square variant="ghost"/>
        @endguest

    </flux:navbar>
</flux:header>

<flux:main class="p-2 md:p-6">
    {{ $slot }}
</flux:main>
    @include('partials.foot')
    <flux:toast />
</body>
</html>
