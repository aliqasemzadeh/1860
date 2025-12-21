<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" dir="rtl">
<head>
    @include('partials.head')
    <script src="https://unpkg.com/smoothscroll-polyfill@0.4.4/dist/smoothscroll.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.5.x/dist/css/glide.core.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.5.x"></script>
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
<flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    @include('partials.sidebar-header')

    <livewire:main.sidebar.categories />

    <flux:sidebar.spacer />

    @include('partials.panels')

    @include('partials.user-dropdown')
    @include('partials.theme-icon')

</flux:sidebar>

<flux:header class="bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
    <flux:navbar class="w-full flex flex-row">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <flux:modal.trigger name="search" shortcut="cmd.f">
            <flux:input as="button" placeholder="{{ __('app.search') }}" icon="magnifying-glass" kbd="⌘K" />
        </flux:modal.trigger>
        <flux:modal name="search" variant="bare" class="w-full max-w-[30rem] my-[12vh] max-h-screen overflow-y-hidden">
            <flux:command class="border-none shadow-lg inline-flex flex-col max-h-[76vh]">
                <flux:command.input placeholder="Search..." closable />
                <flux:command.items>
                    <flux:command.item icon="user-plus" kbd="⌘A">
                        مانیتور
                    </flux:command.item>
                    <flux:command.item icon="document-plus">کیس</flux:command.item>
                    <flux:command.item icon="folder-plus" kbd="⌘⇧N">مودم</flux:command.item>
                    <flux:command.item icon="book-open">تست</flux:command.item>
                    <flux:command.item icon="newspaper">تست </flux:command.item>
                    <flux:command.item icon="cog-6-tooth" kbd="⌘,">تست</flux:command.item>
                </flux:command.items>
            </flux:command>
        </flux:modal>

        <livewire:main.sidebar.basket />

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
