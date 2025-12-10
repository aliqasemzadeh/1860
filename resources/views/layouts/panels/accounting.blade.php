<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" dir="rtl">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
<flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    @include('partials.sidebar-header')

    <flux:sidebar.search placeholder="{{ __('app.search_placeholder') }}" />

    <flux:sidebar.nav>
        <flux:sidebar.item icon="home" href="{{ route('accounting.dashboard.index') }}" wire:navigate>{{ __('app.dashboard') }}</flux:sidebar.item>
        <flux:sidebar.group expandable heading="{{ __('app.banks') }}" class="grid" :expanded="request()->routeIs('accounting.bank.*')">
            @can('accounting_bank_index')
            <flux:sidebar.item href="{{ route('accounting.bank.index') }}" wire:navigate>{{ __('app.banks') }}</flux:sidebar.item>
            @endcan
            @can('accounting_bank_remittance_index')
            <flux:sidebar.item href="{{ route('accounting.bank.remittance.index') }}" wire:navigate>{{ __('app.accounting_bank_remittance_index') }}</flux:sidebar.item>
            @endcan
            @can('accounting_bank_transaction_index')
            <flux:sidebar.item href="{{ route('accounting.bank.transaction.index') }}" wire:navigate>{{ __('app.accounting_bank_transaction_index') }}</flux:sidebar.item>
            @endcan
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

