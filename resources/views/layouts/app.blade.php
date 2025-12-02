<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" dir="rtl">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
<flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    @include('partials.sidebar-header')

    <livewire:main.sidebar.categories />

    <flux:sidebar.spacer />

    @include('partials.panels')

    @include('partials.user-dropdown')
    <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
        <flux:radio value="light" icon="sun"></flux:radio>
        <flux:radio value="dark" icon="moon"></flux:radio>
        <flux:radio value="system" icon="computer-desktop"></flux:radio>
    </flux:radio.group>
</flux:sidebar>

<flux:header class="bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
    <flux:navbar class="w-full flex flex-row">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <flux:sidebar.search placeholder="{{ __('app.search') }}" />
        <flux:button icon="shopping-cart" href="{{ route('login') }}" />
        @auth
        <flux:button icon="user" href="{{ route('logout') }}" />
        @endauth

        @guest
        <flux:button icon="user" href="{{ route('login') }}" />
        @endguest

    </flux:navbar>
</flux:header>

<flux:main>
    {{ $slot }}
</flux:main>
@include('partials.foot')
</body>
</html>
