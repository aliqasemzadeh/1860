<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" dir="rtl">
<head>
    @include('partials.head')
    <x-seo :title="$title ?? null" noindex />
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
<flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    @include('partials.sidebar-header')

    <flux:sidebar.search placeholder="Search..." />

    <flux:sidebar.nav>
        <flux:sidebar.item icon="gauge" href="{{ route('panel.user.dashboard.index') }}" wire:navigate :current="request()->routeIs('panel.user.dashboard.*')">{{ __('app.dashboard') }}</flux:sidebar.item>
        <flux:sidebar.item icon="bring-to-front" href="{{ route('panel.user.order.index') }}" wire:navigate :current="request()->routeIs('panel.user.order.*')">{{ __('app.my_orders') }}</flux:sidebar.item>
        <flux:sidebar.item icon="map-pin" href="{{ route('panel.user.address.index') }}" wire:navigate :current="request()->routeIs('panel.user.address.*')">{{ __('app.addresses') }}</flux:sidebar.item>
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    @include('partials.panels')

    @include('partials.user-dropdown')
    @include('partials.theme-icon')
</flux:sidebar>

<flux:header class="block! bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
    @include('partials.user-navbar')
</flux:header>

<flux:main>
    {{ $slot }}
</flux:main>
@include('partials.foot')
</body>
</html>
