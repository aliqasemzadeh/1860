<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" dir="rtl">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
<flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    @include('partials.sidebar-header')

    <flux:sidebar.search placeholder="Search..." />

    <flux:sidebar.nav>
        <flux:sidebar.item icon="gauge" href="{{ route('shop.dashboard.index') }}" wire:navigate>{{ __('app.dashboard') }}</flux:sidebar.item>
        <flux:sidebar.item icon="boxes" href="{{ route('shop.product.index') }}" wire:navigate>{{ __('app.products') }}</flux:sidebar.item>
        <flux:sidebar.item icon="chart-bar-stacked" href="{{ route('shop.category.index') }}" wire:navigate>{{ __('app.categories') }}</flux:sidebar.item>
        <flux:sidebar.item icon="bring-to-front" href="{{ route('shop.order.index') }}" wire:navigate>{{ __('app.orders') }}</flux:sidebar.item>
        <flux:sidebar.item icon="trello" href="{{ route('shop.brand.index') }}" wire:navigate>{{ __('app.brands') }}</flux:sidebar.item>
        <flux:sidebar.item icon="heart-plus" href="{{ route('shop.warranty.index') }}" wire:navigate>{{ __('app.warranties') }}</flux:sidebar.item>
        <flux:sidebar.item icon="palette" href="{{ route('shop.color.index') }}" wire:navigate>{{ __('app.colors') }}</flux:sidebar.item>

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
