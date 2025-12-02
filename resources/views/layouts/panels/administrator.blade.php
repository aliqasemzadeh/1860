<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" dir="rtl">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
<flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    <flux:sidebar.header>
        <flux:sidebar.brand
            href="#"
            logo="https://fluxui.dev/img/demo/logo.png"
            logo:dark="https://fluxui.dev/img/demo/dark-mode-logo.png"
            name="{{ config('app.name') }}"
        />

        <flux:sidebar.collapse class="lg:hidden" />
    </flux:sidebar.header>

    <flux:sidebar.search placeholder="{{ __('app.search_placeholder') }}" />

    <flux:sidebar.nav>
        <flux:sidebar.item icon="home" href="{{ route('administrator.dashboard.index') }}">{{ __('app.dashboard') }}</flux:sidebar.item>
        <flux:sidebar.group expandable heading="{{ __('app.user_management') }}" class="grid" :expanded="request()->routeIs('administrator.user-management.*')">
            <flux:sidebar.item href="{{ route('administrator.user-management.user.index') }}">{{ __('app.users') }}</flux:sidebar.item>
            <flux:sidebar.item href="{{ route('administrator.user-management.role.index') }}">{{ __('app.roles') }}</flux:sidebar.item>
            <flux:sidebar.item href="{{ route('administrator.user-management.permission.index') }}">{{ __('app.permissions') }}</flux:sidebar.item>
        </flux:sidebar.group>
        <flux:sidebar.group expandable heading="{{ __('app.setting_management') }}" class="grid" :expanded="request()->routeIs('administrator.setting-management.*')">
            <flux:sidebar.item href="{{ route('administrator.setting-management.function.index') }}">{{ __('app.functions') }}</flux:sidebar.item>
            <flux:sidebar.item href="{{ route('administrator.setting-management.option.index') }}">{{ __('app.options') }}</flux:sidebar.item>
        </flux:sidebar.group>
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    @include('partials.panels')

    @include('partials.user-dropdown')
</flux:sidebar>

<flux:header class="block! bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
    @include('partials.user-navbar')

    <flux:navbar scrollable>
        <flux:navbar.item href="#" current>{{ __('app.dashboard') }}</flux:navbar.item>
        <flux:navbar.item badge="32" href="#">{{ __('app.orders') }}</flux:navbar.item>
        <flux:navbar.item href="#">{{ __('app.catalog') }}</flux:navbar.item>
        <flux:navbar.item href="#">{{ __('app.configuration') }}</flux:navbar.item>
    </flux:navbar>
</flux:header>

<flux:main>
    {{ $slot }}
</flux:main>
@include('partials.foot')
</body>
</html>
