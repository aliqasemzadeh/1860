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
            href="{{ route('panel.shop.dashboard.index') }}"
            wire:navigate
            :current="request()->routeIs('panel.shop.dashboard.*')"
        >
            {{ __('general.dashboard') }}
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="boxes"
            href="{{ route('panel.shop.product.index') }}"
            wire:navigate
            :current="request()->routeIs('panel.shop.product.*')"
        >
            {{ __('general.products') }}
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="bring-to-front"
            href="{{ route('panel.shop.order.index') }}"
            wire:navigate
            :current="request()->routeIs('panel.shop.order.*')"
        >
            {{ __('general.orders') }}
        </flux:sidebar.item>

        <flux:sidebar.group
            expandable
            icon="wrench-screwdriver"
            heading="{{ __('general.setting_management') }}"
            class="grid"
            :expanded="request()->routeIs('panel.shop.setting-management.*')"
        >
            <flux:sidebar.item
                href="{{ route('panel.shop.setting-management.category.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.shop.setting-management.category.*')"
            >
                {{ __('general.categories') }}
            </flux:sidebar.item>
            <flux:sidebar.item
                href="{{ route('panel.shop.setting-management.brand.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.shop.setting-management.brand.*')"
            >
                {{ __('general.brands') }}
            </flux:sidebar.item>
            <flux:sidebar.item
                href="{{ route('panel.shop.setting-management.warranty.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.shop.setting-management.warranty.*')"
            >
                {{ __('general.warranties') }}
            </flux:sidebar.item>
            <flux:sidebar.item
                href="{{ route('panel.shop.setting-management.color.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.shop.setting-management.color.*')"
            >
                {{ __('general.colors') }}
            </flux:sidebar.item>
            <flux:sidebar.item
                href="{{ route('panel.shop.setting-management.unit.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.shop.setting-management.unit.*')"
            >
                {{ __('general.units') }}
            </flux:sidebar.item>
            <flux:sidebar.item
                href="{{ route('panel.shop.setting-management.attribute-group.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.shop.setting-management.attribute-group.*')"
            >
                {{ __('general.attribute_groups') }}
            </flux:sidebar.item>
            <flux:sidebar.item
                href="{{ route('panel.shop.setting-management.attribute.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.shop.setting-management.attribute.*')"
            >
                {{ __('general.attributes') }}
            </flux:sidebar.item>
        </flux:sidebar.group>

        <flux:sidebar.group
            expandable
            icon="truck"
            heading="{{ __('general.shipping') }}"
            class="grid"
            :expanded="request()->routeIs('panel.shop.shipping.*')"
        >
            <flux:sidebar.item
                href="{{ route('panel.shop.shipping.method.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.shop.shipping.method.*')"
            >
                {{ __('general.shipping_methods') }}
            </flux:sidebar.item>
            <flux:sidebar.item
                href="{{ route('panel.shop.shipping.zone.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.shop.shipping.zone.*')"
            >
                {{ __('general.shipping_zones') }}
            </flux:sidebar.item>
            <flux:sidebar.item
                href="{{ route('panel.shop.shipping.rate.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.shop.shipping.rate.*')"
            >
                {{ __('general.shipping_rates') }}
            </flux:sidebar.item>
        </flux:sidebar.group>
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    @include('partials.panels')
    @include('partials.user-dropdown')
    @include('partials.theme-icon')
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
