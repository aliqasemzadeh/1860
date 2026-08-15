<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" dir="rtl">
<head>
    @include('partials.head')
    <x-seo :title="$title ?? null" noindex />
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
<flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    @include('partials.sidebar-header')

    <flux:sidebar.search placeholder="{{ __('general.search_placeholder') }}" />

    <flux:sidebar.nav>
        <flux:sidebar.item icon="home" href="{{ route('panel.administrator.dashboard.index') }}" wire:navigate>{{ __('general.dashboard') }}</flux:sidebar.item>
        <flux:sidebar.group expandable heading="{{ __('general.user_management') }}" class="grid" :expanded="request()->routeIs('administrator.user-management.*')">
            <flux:sidebar.item href="{{ route('panel.administrator.user-management.user.index') }}" wire:navigate>{{ __('general.users') }}</flux:sidebar.item>
            <flux:sidebar.item href="{{ route('panel.administrator.user-management.role.index') }}" wire:navigate>{{ __('general.roles') }}</flux:sidebar.item>
            <flux:sidebar.item href="{{ route('panel.administrator.user-management.permission.index') }}" wire:navigate>{{ __('general.permissions') }}</flux:sidebar.item>
        </flux:sidebar.group>
        <flux:sidebar.group expandable heading="{{ __('general.setting_management') }}" class="grid" :expanded="request()->routeIs('panel.administrator.setting-management.*')">
            <flux:sidebar.item href="{{ route('panel.administrator.setting-management.function.index') }}" wire:navigate>{{ __('general.function') }}</flux:sidebar.item>
            <flux:sidebar.item href="{{ route('panel.administrator.setting-management.backup.index') }}" wire:navigate>{{ __('general.backup') }}</flux:sidebar.item>
            <flux:sidebar.item href="{{ route('panel.administrator.setting-management.option.index') }}" wire:navigate>{{ __('general.options') }}</flux:sidebar.item>
            <flux:sidebar.item href="{{ route('log-viewer.index') }}" target="_blank">{{ __('general.log_viewer') }}</flux:sidebar.item>
        </flux:sidebar.group>
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
