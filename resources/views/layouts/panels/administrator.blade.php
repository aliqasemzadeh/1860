<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" dir="rtl">
<head>
    @include('partials.head')
    <x-seo :title="$title ?? null" noindex />
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
<flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-e border-zinc-200 dark:border-zinc-700">
    @include('partials.sidebar-header')

    <flux:sidebar.nav>
        <flux:sidebar.item
            icon="gauge"
            href="{{ route('panel.administrator.dashboard.index') }}"
            wire:navigate
            :current="request()->routeIs('panel.administrator.dashboard.*')"
        >
            {{ __('general.dashboard') }}
        </flux:sidebar.item>

        <flux:sidebar.group
            expandable
            icon="users"
            heading="{{ __('general.user_management') }}"
            class="grid"
            :expanded="request()->routeIs('panel.administrator.user-management.*')"
        >
            <flux:sidebar.item
                href="{{ route('panel.administrator.user-management.user.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.administrator.user-management.user.*')"
            >
                {{ __('general.users') }}
            </flux:sidebar.item>
            <flux:sidebar.item
                href="{{ route('panel.administrator.user-management.role.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.administrator.user-management.role.*')"
            >
                {{ __('general.roles') }}
            </flux:sidebar.item>
            <flux:sidebar.item
                href="{{ route('panel.administrator.user-management.permission.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.administrator.user-management.permission.*')"
            >
                {{ __('general.permissions') }}
            </flux:sidebar.item>
        </flux:sidebar.group>

        <flux:sidebar.group
            expandable
            icon="wrench-screwdriver"
            heading="{{ __('general.setting_management') }}"
            class="grid"
            :expanded="request()->routeIs('panel.administrator.setting-management.*')"
        >
            <flux:sidebar.item
                href="{{ route('panel.administrator.setting-management.function.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.administrator.setting-management.function.*')"
            >
                {{ __('general.function') }}
            </flux:sidebar.item>
            <flux:sidebar.item
                href="{{ route('panel.administrator.setting-management.backup.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.administrator.setting-management.backup.*')"
            >
                {{ __('general.backup') }}
            </flux:sidebar.item>
            <flux:sidebar.item
                href="{{ route('panel.administrator.setting-management.option.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.administrator.setting-management.option.*')"
            >
                {{ __('general.options') }}
            </flux:sidebar.item>
            <flux:sidebar.item
                href="{{ route('log-viewer.index') }}"
                target="_blank"
            >
                {{ __('general.log_viewer') }}
            </flux:sidebar.item>
        </flux:sidebar.group>
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    <div class="border-t border-zinc-200/80 pt-2 space-y-2 dark:border-zinc-800">
        @include('partials.user-dropdown')
        @include('partials.theme-icon')
    </div>
</flux:sidebar>

<flux:header sticky class="lg:hidden bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
    @include('partials.user-navbar')
</flux:header>

<flux:main class="p-3 md:p-6">
    {{ $slot }}
</flux:main>
@include('partials.foot')
</body>
</html>
