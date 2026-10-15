@php
    use Illuminate\Support\Facades\Storage;
@endphp

<x-slot name="title">
    {{ config('app.name') }}
</x-slot>

<div>
    <div class="mx-auto max-w-7xl px-4 2xl:px-0 py-4 md:py-8 space-y-12 md:space-y-20">

        {{-- ═══════════════════════ Hero Bento Grid ═══════════════════════ --}}
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-3 lg:grid-rows-2 lg:h-[34rem]">

            {{-- Main flagship banner --}}
            <div class="group relative overflow-hidden rounded-[2rem] border border-zinc-200/80 bg-zinc-950 text-white shadow-xl dark:border-zinc-800/80 lg:col-span-2 lg:row-span-2">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(45,212,191,0.35),_transparent_55%),radial-gradient(ellipse_at_bottom_left,_rgba(99,102,241,0.25),_transparent_55%)]"></div>
                <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.04)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.04)_1px,transparent_1px)] bg-[size:3rem_3rem] [mask-image:radial-gradient(ellipse_at_center,black_30%,transparent_75%)]"></div>
                <div class="pointer-events-none absolute -bottom-32 -start-16 size-96 rounded-full bg-teal-500/20 blur-3xl transition-transform duration-1000 group-hover:scale-125"></div>

                <div class="relative flex h-full flex-col justify-between gap-10 p-7 md:p-12">
                    <div class="space-y-5">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold backdrop-blur">
                            <span class="relative flex size-2">
                                <span class="absolute inline-flex size-full animate-ping rounded-full bg-teal-400 opacity-75"></span>
                                <span class="relative inline-flex size-2 rounded-full bg-teal-400"></span>
                            </span>
                            {{ __('general.home_hero_badge') }}
                        </span>

                        <h1 class="max-w-xl text-3xl font-black leading-[1.2] tracking-tight md:text-5xl">
                            {{ __('general.home_hero_title') }}
                        </h1>

                        <p class="max-w-lg text-sm leading-7 text-zinc-300 md:text-lg">
                            {{ __('general.home_hero_subtitle') }}
                        </p>

                        <div class="flex flex-wrap gap-3 pt-1">
                            <flux:button href="{{ route('search.index') }}" wire:navigate variant="primary" color="teal" icon="shopping-bag" class="!rounded-full shadow-lg shadow-teal-500/30">
                                {{ __('general.home_hero_browse') }}
                            </flux:button>
                            <flux:button href="{{ route('post.index') }}" wire:navigate variant="ghost" icon="newspaper" class="!rounded-full !text-white hover:!bg-white/10">
                                {{ __('general.home_hero_blog') }}
                            </flux:button>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 md:gap-3">
                        @foreach([
                            ['icon' => 'truck', 'label' => __('general.home_hero_stat_shipping')],
                            ['icon' => 'shield-check', 'label' => __('general.home_hero_stat_original')],
                            ['icon' => 'badge-check', 'label' => __('general.home_hero_stat_warranty')],
                        ] as $stat)
                            <div class="flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-3 py-2.5 backdrop-blur-sm md:gap-3 md:px-4 md:py-3">
                                <flux:icon :name="$stat['icon']" class="size-4 shrink-0 text-teal-300 md:size-5" />
                                <span class="text-[11px] font-semibold text-zinc-100 md:text-sm">{{ $stat['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Promo: special offers --}}
            <a
                href="{{ route('search.index') }}"
                wire:navigate
                class="group relative flex min-h-[14rem] flex-col justify-end overflow-hidden rounded-[2rem] border border-zinc-200/80 bg-gradient-to-br from-rose-50 via-white to-orange-50 p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl dark:border-zinc-800/80 dark:from-rose-950/40 dark:via-zinc-900 dark:to-orange-950/30"
            >
                <div class="pointer-events-none absolute -end-10 -top-10 size-48 rounded-full bg-rose-300/30 blur-3xl dark:bg-rose-500/10"></div>

                @if($this->heroOfferProduct?->file_path)
                    <img
                        src="{{ Storage::url($this->heroOfferProduct->file_path) }}"
                        alt="{{ product_image_alt($this->heroOfferProduct) }}"
                        loading="lazy"
                        decoding="async"
                        class="pointer-events-none absolute -top-4 start-4 w-36 rotate-[-8deg] object-contain opacity-90 drop-shadow-2xl transition-transform duration-700 group-hover:-translate-y-2 group-hover:rotate-[-4deg] group-hover:scale-110 md:w-44"
                    />
                @else
                    <flux:icon.percent-badge class="pointer-events-none absolute -top-6 start-4 size-40 text-rose-200/70 transition-transform duration-700 group-hover:rotate-12 group-hover:scale-110 dark:text-rose-900/40" />
                @endif

                <div class="relative space-y-2">
                    <flux:badge color="red" size="sm" rounded icon="flame">{{ __('general.discount') }}</flux:badge>
                    <flux:heading size="lg" class="font-extrabold">{{ __('general.special_offers') }}</flux:heading>
                    <flux:text class="line-clamp-2 text-sm text-zinc-600 dark:text-zinc-300">{{ __('general.home_promo_offers_text') }}</flux:text>
                    <span class="inline-flex items-center gap-1.5 pt-1 text-sm font-bold text-rose-600 dark:text-rose-400">
                        {{ __('general.view_all') }}
                        <flux:icon.arrow-left class="size-4 transition-transform group-hover:-translate-x-1" />
                    </span>
                </div>
            </a>

            {{-- Promo: buying guides / blog --}}
            <a
                href="{{ $this->heroPost?->url ?? route('post.index') }}"
                wire:navigate
                class="group relative flex min-h-[14rem] flex-col justify-end overflow-hidden rounded-[2rem] border border-zinc-200/80 bg-zinc-100 p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl dark:border-zinc-800/80 dark:bg-zinc-900"
            >
                @if($this->heroPost?->featured_image_url)
                    <img
                        src="{{ $this->heroPost->featured_image_url }}"
                        alt="{{ $this->heroPost->title }}"
                        loading="lazy"
                        decoding="async"
                        class="absolute inset-0 size-full object-cover transition-transform duration-700 group-hover:scale-110"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/90 via-zinc-950/40 to-transparent"></div>
                @else
                    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,_rgba(99,102,241,0.25),_transparent_60%)]"></div>
                    <flux:icon.book-open class="pointer-events-none absolute -top-4 end-4 size-36 text-indigo-200/70 transition-transform duration-700 group-hover:-rotate-6 group-hover:scale-110 dark:text-indigo-900/40" />
                @endif

                <div class="relative space-y-2 {{ $this->heroPost?->featured_image_url ? 'text-white' : '' }}">
                    <flux:badge color="indigo" size="sm" rounded icon="book-open">{{ __('general.blog') }}</flux:badge>
                    <flux:heading size="lg" class="font-extrabold {{ $this->heroPost?->featured_image_url ? '!text-white' : '' }}">
                        {{ $this->heroPost?->title ?? __('general.home_promo_blog_title') }}
                    </flux:heading>
                    <flux:text class="line-clamp-2 text-sm {{ $this->heroPost?->featured_image_url ? '!text-zinc-200' : 'text-zinc-600 dark:text-zinc-300' }}">
                        {{ $this->heroPost?->summary ?? __('general.home_promo_blog_text') }}
                    </flux:text>
                    <span class="inline-flex items-center gap-1.5 pt-1 text-sm font-bold {{ $this->heroPost?->featured_image_url ? 'text-teal-300' : 'text-indigo-600 dark:text-indigo-400' }}">
                        {{ __('general.read_article') }}
                        <flux:icon.arrow-left class="size-4 transition-transform group-hover:-translate-x-1" />
                    </span>
                </div>
            </a>
        </section>

        {{-- ═══════════════════════ Categories ═══════════════════════ --}}
        @if($this->categories->isNotEmpty())
            <section>
                <div class="mb-5 flex items-center gap-3">
                    <span class="h-8 w-1.5 rounded-full bg-gradient-to-b from-teal-400 to-teal-600"></span>
                    <flux:heading size="xl" class="font-extrabold">{{ __('general.shop_by_category') }}</flux:heading>
                </div>

                <div class="-mx-4 flex snap-x gap-3 overflow-x-auto px-4 pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    @foreach($this->categories as $category)
                        <a
                            href="{{ $category->url }}"
                            wire:navigate
                            wire:key="home-category-{{ $category->id }}"
                            class="group flex shrink-0 snap-start items-center gap-2.5 rounded-2xl border border-zinc-200/80 bg-white/80 px-4 py-2.5 text-sm font-semibold text-zinc-700 shadow-sm backdrop-blur transition-all hover:-translate-y-0.5 hover:border-teal-300 hover:text-teal-700 hover:shadow-lg hover:shadow-teal-500/10 dark:border-zinc-800/80 dark:bg-zinc-900/70 dark:text-zinc-200 dark:hover:border-teal-700 dark:hover:text-teal-300"
                        >
                            <span class="flex size-8 items-center justify-center rounded-xl bg-teal-50 text-teal-600 transition-colors group-hover:bg-teal-600 group-hover:text-white dark:bg-teal-950/60 dark:text-teal-300">
                                @if($category->icon)
                                    <flux:icon :name="$category->icon" class="size-4" />
                                @else
                                    <flux:icon.cube class="size-4" />
                                @endif
                            </span>
                            <span>{{ $category->name }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ═══════════════════════ Brand Islands (GPlus, Meva, ...) ═══════════════════════ --}}
        <livewire:main.content.box.index :key="'home-brand-boxes'" lazy />

        {{-- ═══════════════════════ Special offers ═══════════════════════ --}}
        @if($this->discountedProducts->isNotEmpty())
            <section>
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-950/50 dark:text-red-400">
                            <flux:icon.percent-badge class="size-6" />
                        </span>
                        <flux:heading size="xl" class="font-extrabold">{{ __('general.special_offers') }}</flux:heading>
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

        {{-- ═══════════════════════ Newest products ═══════════════════════ --}}
        <section>
            <div class="mb-5 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-600 dark:bg-teal-950/50 dark:text-teal-400">
                        <flux:icon.sparkles class="size-6" />
                    </span>
                    <flux:heading size="xl" class="font-extrabold">{{ __('general.newest_products') }}</flux:heading>
                </div>

                <div class="flex items-center gap-2">
                    @if($this->newestProducts->isNotEmpty())
                        <flux:carousel.controls name="home-newest-carousel" />
                    @endif
                    <flux:button href="{{ route('search.index') }}" wire:navigate variant="ghost" size="sm" icon:trailing="chevron-left" class="hidden sm:inline-flex">
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

        {{-- ═══════════════════════ Editorial: exactly two featured articles ═══════════════════════ --}}
        @if($this->latestPosts->isNotEmpty())
            <section>
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                            <flux:icon.newspaper class="size-6" />
                        </span>
                        <div>
                            <flux:heading size="xl" class="font-extrabold">{{ __('general.featured_articles') }}</flux:heading>
                            <flux:text class="hidden text-sm text-zinc-500 sm:block">{{ __('general.featured_articles_subtitle') }}</flux:text>
                        </div>
                    </div>
                    <flux:button href="{{ route('post.index') }}" wire:navigate variant="ghost" size="sm" icon:trailing="chevron-left">
                        {{ __('general.view_all') }}
                    </flux:button>
                </div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    @foreach($this->latestPosts->take(2) as $post)
                        <a
                            href="{{ $post->url }}"
                            wire:navigate
                            wire:key="home-post-{{ $post->id }}"
                            class="group flex flex-col overflow-hidden rounded-[1.75rem] border border-zinc-200/80 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-indigo-500/10 dark:border-zinc-800/80 dark:bg-zinc-900 sm:flex-row"
                        >
                            <div class="relative aspect-[16/10] shrink-0 overflow-hidden bg-zinc-100 dark:bg-zinc-800 sm:aspect-auto sm:w-2/5">
                                @if($post->featured_image_url)
                                    <img
                                        src="{{ $post->featured_image_url }}"
                                        alt="{{ $post->title }}"
                                        loading="lazy"
                                        decoding="async"
                                        class="size-full object-cover transition-transform duration-700 group-hover:scale-110"
                                    />
                                @else
                                    <div class="flex size-full items-center justify-center text-zinc-300 dark:text-zinc-600">
                                        <flux:icon.newspaper class="size-12" />
                                    </div>
                                @endif

                                <div class="absolute start-3 top-3 flex items-center gap-1.5">
                                    @if($post->tags->isNotEmpty())
                                        <flux:badge size="sm" color="indigo" rounded class="backdrop-blur">{{ $post->tags->first()->name }}</flux:badge>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-grow flex-col p-5 md:p-6">
                                <div class="mb-3 flex flex-wrap items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-1 font-medium dark:bg-zinc-800">
                                        <flux:icon.clock class="size-3.5" />
                                        {{ $post->reading_time }} {{ __('general.min_read') }}
                                    </span>
                                    @if($post->published_at)
                                        <span class="inline-flex items-center gap-1">
                                            <flux:icon.calendar class="size-3.5" />
                                            {{ jalali($post->published_at, 'Y/m/d') }}
                                        </span>
                                    @endif
                                </div>

                                <flux:heading size="lg" class="mb-2 line-clamp-2 font-extrabold leading-snug transition-colors group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                                    {{ $post->title }}
                                </flux:heading>

                                @if($post->summary)
                                    <flux:text class="line-clamp-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                                        {{ $post->summary }}
                                    </flux:text>
                                @endif

                                <span class="mt-auto inline-flex items-center gap-2 pt-5 text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ __('general.read_article') }}
                                    <span class="flex size-7 items-center justify-center rounded-full bg-indigo-50 transition-all duration-300 group-hover:-translate-x-1 group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-950/60">
                                        <flux:icon.arrow-left class="size-3.5" />
                                    </span>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ═══════════════════════ Home Footer (Blog, Contact, Socials, eNamad) ═══════════════════════ --}}
        @php($general = app(\App\Settings\GeneralSettings::class))
        <footer class="overflow-hidden rounded-[2rem] border border-zinc-200/80 bg-zinc-50/80 p-6 md:p-10 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900/70">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-12 items-center">
                {{-- Brand & Quick Links (Blog, Contact Us) --}}
                <div class="md:col-span-5 space-y-4">
                    <div class="flex items-center gap-3">
                        @if ($general->logo_path)
                            <img src="{{ $general->logoUrl() }}" alt="{{ $general->title }}" class="h-10 w-auto object-contain" />
                        @endif
                        <div>
                            <flux:heading size="lg" class="font-extrabold">{{ $general->title }}</flux:heading>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('general.home_hero_badge') }}</flux:text>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <flux:button
                            href="{{ route('post.index') }}"
                            wire:navigate
                            variant="filled"
                            size="sm"
                            icon="newspaper"
                            class="!rounded-xl"
                        >
                            {{ __('general.blog') }}
                        </flux:button>

                        <flux:button
                            href="{{ route('contact.index') }}"
                            wire:navigate
                            variant="filled"
                            size="sm"
                            icon="mail"
                            class="!rounded-xl"
                        >
                            {{ __('general.contact_us') }}
                        </flux:button>
                    </div>
                </div>

                {{-- Follow Us (Socials) --}}
                <div class="md:col-span-4">
                    <livewire:main.sidebar.socials :key="'home-footer-socials'" defer />
                </div>

                {{-- eNamad Trust Seal --}}
                <div class="md:col-span-3 flex md:justify-end">
                    @include('partials.enamad')
                </div>
            </div>
        </footer>
    </div>
</div>
