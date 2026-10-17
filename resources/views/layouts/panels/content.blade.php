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
        @can('content_post_index')
            <flux:sidebar.item
                icon="newspaper"
                href="{{ route('panel.content.post.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.content.post.*')"
            >
                {{ __('general.posts') }}
            </flux:sidebar.item>
        @endcan

        @can('content_box_index')
            <flux:sidebar.item
                icon="layout-grid"
                href="{{ route('panel.content.box.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.content.box.*')"
            >
                {{ __('general.boxes') }}
            </flux:sidebar.item>
        @endcan

        @can('content_banner_index')
            <flux:sidebar.item
                icon="images"
                href="{{ route('panel.content.banner.index') }}"
                wire:navigate
                :current="request()->routeIs('panel.content.banner.*')"
            >
                {{ __('general.banners') }}
            </flux:sidebar.item>
        @endcan
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
