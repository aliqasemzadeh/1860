<x-slot name="title">
    {{ config('app.name') }}
</x-slot>

<div>
    <div class="mx-auto max-w-7xl px-4 2xl:px-0 py-6 md:py-10 space-y-12 md:space-y-16">

        {{-- Hero --}}
        <section class="relative overflow-hidden rounded-3xl border border-zinc-200/70 dark:border-zinc-800 bg-gradient-to-br from-teal-50 via-white to-zinc-100 dark:from-teal-950/40 dark:via-zinc-900 dark:to-zinc-950 shadow-sm">
            <div class="pointer-events-none absolute -top-24 -start-24 size-72 rounded-full bg-teal-200/40 dark:bg-teal-500/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 -end-24 size-72 rounded-full bg-emerald-200/40 dark:bg-emerald-500/10 blur-3xl"></div>

            <div class="relative grid gap-8 p-6 md:p-10 lg:grid-cols-5 lg:items-center">
                <div class="space-y-5 lg:col-span-3">
                    <flux:badge color="teal" rounded icon="sparkles">{{ __('general.home_hero_badge') }}</flux:badge>

                    <flux:heading size="2xl" level="1" class="leading-tight">
                        {{ __('general.home_hero_title') }}
                    </flux:heading>

                    <flux:subheading size="lg" class="max-w-xl text-zinc-600 dark:text-zinc-300">
                        {{ __('general.home_hero_subtitle') }}
                    </flux:subheading>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <flux:button
                            href="{{ route('search.index') }}"
                            wire:navigate
                            variant="primary"
                            color="teal"
                            icon="shopping-bag"
                        >
                            {{ __('general.home_hero_browse') }}
                        </flux:button>

                        <flux:button
                            href="{{ route('post.index') }}"
                            wire:navigate
                            variant="ghost"
                            icon="newspaper"
                        >
                            {{ __('general.home_hero_blog') }}
                        </flux:button>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3 lg:col-span-2 lg:grid-cols-1">
                    @foreach([
                        ['icon' => 'truck', 'label' => __('general.home_feature_fast_shipping')],
                        ['icon' => 'shield-check', 'label' => __('general.home_feature_original')],
                        ['icon' => 'headphones', 'label' => __('general.home_feature_support')],
                    ] as $feature)
                        <div class="flex flex-col items-center gap-2 rounded-2xl border border-zinc-200/70 dark:border-zinc-700/70 bg-white/80 dark:bg-zinc-800/70 backdrop-blur p-3 text-center lg:flex-row lg:text-start lg:p-4">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-600 dark:bg-teal-900/40 dark:text-teal-300">
                                <flux:icon :name="$feature['icon']" class="size-5" />
                            </span>
                            <flux:text class="text-xs font-semibold text-zinc-700 dark:text-zinc-200 md:text-sm">
                                {{ $feature['label'] }}
                            </flux:text>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Categories --}}
        @if($this->categories->isNotEmpty())
            <section>
                <div class="mb-5 flex items-center gap-3">
                    <div class="h-8 w-2 rounded-full bg-teal-600"></div>
                    <flux:heading size="xl">{{ __('general.shop_by_category') }}</flux:heading>
                </div>

                <div class="-mx-4 flex gap-3 overflow-x-auto px-4 pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    @foreach($this->categories as $category)
                        <a
                            href="{{ $category->url }}"
                            wire:navigate
                            wire:key="home-category-{{ $category->id }}"
                            class="group flex shrink-0 items-center gap-2 rounded-full border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-teal-300 hover:text-teal-700 hover:shadow dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-teal-700 dark:hover:text-teal-300"
                        >
                            @if($category->icon)
                                <flux:icon :name="$category->icon" class="size-5 text-teal-600 dark:text-teal-400" />
                            @endif
                            <span>{{ $category->name }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Brand boxes --}}
        <livewire:main.content.box.index :key="'home-brand-boxes'" />

        {{-- Special offers --}}
        @if($this->discountedProducts->isNotEmpty())
            <section>
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-950/50 dark:text-red-400">
                            <flux:icon.percent-badge class="size-6" />
                        </span>
                        <flux:heading size="xl">{{ __('general.special_offers') }}</flux:heading>
                    </div>
                    <flux:carousel.controls name="home-offers-carousel" />
                </div>

                <flux:carousel name="home-offers-carousel" class="-mx-4" :arrows="false" track:class="px-4 scroll-px-4 gap-4 pb-2">
                    @foreach($this->discountedProducts as $product)
                        <flux:carousel.slide wire:key="home-offer-{{ $product->id }}" class="w-[70%] sm:w-1/2 md:w-1/3 lg:w-1/4 xl:w-1/5">
                            @include('partials.product-card', ['product' => $product])
                        </flux:carousel.slide>
                    @endforeach
                </flux:carousel>
            </section>
        @endif

        {{-- Newest products --}}
        <section>
            <div class="mb-5 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600 dark:bg-teal-950/50 dark:text-teal-400">
                        <flux:icon.sparkles class="size-6" />
                    </span>
                    <flux:heading size="xl">{{ __('general.newest_products') }}</flux:heading>
                </div>

                <div class="flex items-center gap-2">
                    @if($this->newestProducts->isNotEmpty())
                        <flux:carousel.controls name="home-newest-carousel" />
                    @endif
                    <flux:button
                        href="{{ route('search.index') }}"
                        wire:navigate
                        variant="ghost"
                        size="sm"
                        icon:trailing="chevron-left"
                        class="hidden sm:inline-flex"
                    >
                        {{ __('general.view_all') }}
                    </flux:button>
                </div>
            </div>

            @if($this->newestProducts->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-zinc-200 py-16 text-center dark:border-zinc-700">
                    <span class="mb-4 flex size-16 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500">
                        <flux:icon.shopping-bag class="size-8" />
                    </span>
                    <flux:heading size="lg" class="mb-2">{{ __('general.no_products_found') }}</flux:heading>
                    <flux:text class="text-zinc-500">{{ __('general.no_products_found_description') }}</flux:text>
                </div>
            @else
                <flux:carousel name="home-newest-carousel" class="-mx-4" :arrows="false" track:class="px-4 scroll-px-4 gap-4 pb-2">
                    @foreach($this->newestProducts as $product)
                        <flux:carousel.slide wire:key="home-newest-{{ $product->id }}" class="w-[70%] sm:w-1/2 md:w-1/3 lg:w-1/4 xl:w-1/5">
                            @include('partials.product-card', ['product' => $product])
                        </flux:carousel.slide>
                    @endforeach
                </flux:carousel>
            @endif
        </section>

        {{-- Latest posts --}}
        @if($this->latestPosts->isNotEmpty())
            <section>
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                            <flux:icon.newspaper class="size-6" />
                        </span>
                        <flux:heading size="xl">{{ __('general.latest_posts') }}</flux:heading>
                    </div>
                    <flux:button
                        href="{{ route('post.index') }}"
                        wire:navigate
                        variant="ghost"
                        size="sm"
                        icon:trailing="chevron-left"
                    >
                        {{ __('general.view_all') }}
                    </flux:button>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    @foreach($this->latestPosts as $post)
                        <a
                            href="{{ $post->url }}"
                            wire:navigate
                            wire:key="home-post-{{ $post->id }}"
                            class="group flex flex-col overflow-hidden rounded-3xl border border-zinc-200/80 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
                        >
                            <div class="relative aspect-[16/10] overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                                @if($post->featured_image_url)
                                    <img
                                        src="{{ $post->featured_image_url }}"
                                        alt="{{ $post->title }}"
                                        loading="lazy"
                                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    />
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-zinc-300 dark:text-zinc-600">
                                        <flux:icon.newspaper class="size-12" />
                                    </div>
                                @endif

                                @if($post->tags->isNotEmpty())
                                    <div class="absolute top-3 start-3">
                                        <flux:badge size="sm" rounded class="bg-white/90 backdrop-blur dark:bg-zinc-900/90">
                                            {{ $post->tags->first()->name }}
                                        </flux:badge>
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-grow flex-col p-5">
                                @if($post->published_at)
                                    <div class="mb-2 flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                        <flux:icon.calendar class="size-3.5" />
                                        <span>{{ jalali($post->published_at, 'Y/m/d') }}</span>
                                    </div>
                                @endif

                                <flux:heading size="lg" class="mb-2 line-clamp-2 leading-snug transition-colors group-hover:text-teal-600 dark:group-hover:text-teal-400">
                                    {{ $post->title }}
                                </flux:heading>

                                @if($post->summary)
                                    <flux:text class="line-clamp-2 text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ $post->summary }}
                                    </flux:text>
                                @endif

                                <span class="mt-auto inline-flex items-center gap-1.5 pt-4 text-xs font-bold text-teal-600 dark:text-teal-400">
                                    {{ __('general.read_more') }}
                                    <flux:icon.chevron-left class="size-3.5 transition-transform group-hover:-translate-x-1" />
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
