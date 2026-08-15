<div>
    <x-slot name="title">
        {{ $this->box->title_fa }} - {{ config('app.name') }}
    </x-slot>

    @php
        $theme = $this->box->color_theme ?? [];
        $bgColor = $theme['bg'] ?? '#ffffff';
        $textColor = $theme['text'] ?? '#000000';
        $accentColor = $theme['accent'] ?? '#3b82f6';
        $logoUrl = $this->box->getFirstMediaUrl('box_images');
    @endphp

    <div
        class="relative w-full py-12 md:py-16 rounded-b-[2.5rem] md:rounded-b-[3.5rem] overflow-hidden mb-10 shadow-lg bg-gradient-to-br from-[var(--box-bg)] to-[var(--box-accent)] dark:from-zinc-900 dark:via-zinc-900/95 dark:to-zinc-950 dark:border-b dark:border-zinc-800/80"
        style="--box-bg: {{ $bgColor }}; --box-accent: {{ $accentColor }}; --box-text: {{ $textColor }};"
    >
        <div class="mx-auto max-w-7xl px-4 2xl:px-0 flex flex-col items-center text-center relative z-10">
            <flux:breadcrumbs class="mb-6 text-[var(--box-text)] dark:text-zinc-300 [&_a]:text-[var(--box-text)] dark:[&_a]:text-zinc-300 [&_span]:text-[var(--box-text)] dark:[&_span]:text-zinc-400">
                <flux:breadcrumbs.item href="{{ route('home') }}">{{ __('general.home') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->box->title_fa }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            @if ($logoUrl)
                <img
                    src="{{ $logoUrl }}"
                    alt="{{ $this->box->title_fa }}"
                    class="h-16 w-16 md:h-24 md:w-24 object-contain rounded-2xl bg-white/95 dark:bg-zinc-800/95 p-2 shadow-lg mb-4 border border-zinc-200/50 dark:border-zinc-700/60"
                />
            @endif

            <flux:heading size="2xl" class="text-[var(--box-text)] dark:text-zinc-50 font-bold text-2xl md:text-3xl">
                {{ $this->box->title_fa }}
            </flux:heading>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 2xl:px-0 mb-16">
        <flux:tab.group>
            <div class="flex justify-center md:justify-start mb-8">
                <flux:tabs variant="segmented" class="dark:bg-zinc-900 dark:border-zinc-800">
                    <flux:tab name="products" icon="package">{{ __('general.products') }}</flux:tab>
                    <flux:tab name="articles" icon="book-open">{{ __('general.articles') }}</flux:tab>
                </flux:tabs>
            </div>

            <flux:tab.panel name="products">
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                    @forelse($this->products as $product)
                        <a
                            href="{{ $product->url }}"
                            wire:navigate
                            wire:key="view-product-{{ $product->id }}"
                            class="group flex flex-col justify-between bg-white dark:bg-zinc-900 rounded-3xl p-3 md:p-4 shadow-sm hover:shadow-xl dark:hover:shadow-zinc-950/60 transition-all duration-300 border border-zinc-200/70 dark:border-zinc-800 hover:-translate-y-1"
                        >
                            <div class="aspect-square rounded-2xl overflow-hidden mb-4 bg-zinc-50 dark:bg-zinc-800 relative">
                                @if($product->file_path)
                                    <img
                                        src="{{ Storage::url($product->file_path) }}"
                                        alt="{{ $product->name }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    />
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-zinc-300 dark:text-zinc-600">
                                        <flux:icon.image size="lg" />
                                    </div>
                                @endif

                                @if($product->sale_price && $product->price && $product->sale_price < $product->price)
                                    <div class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-md shadow-sm">
                                        {{ __('general.discount') }}
                                    </div>
                                @endif
                            </div>

                            <flux:heading size="md" class="line-clamp-2 min-h-[3rem] mb-4 text-zinc-900 dark:text-zinc-100 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors leading-snug">
                                {{ $product->name }}
                            </flux:heading>

                            <div class="flex items-center justify-between border-t border-zinc-100 dark:border-zinc-800 pt-3 mt-auto">
                                @if($product->effective_price)
                                    <div class="font-bold text-base md:text-lg text-zinc-900 dark:text-zinc-100">
                                        {{ number_format($product->effective_price, 0) }}
                                        <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">{{ __('general.toman') }}</span>
                                    </div>
                                @else
                                    <div class="text-zinc-400 dark:text-zinc-500 text-xs">{{ __('general.unavailable') }}</div>
                                @endif
                                <flux:button size="xs" variant="primary" square icon="chevron-left" color="teal" />
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full py-20 flex flex-col items-center justify-center text-center">
                            <div class="h-16 w-16 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 dark:text-zinc-500 mb-4 shadow-sm">
                                <flux:icon.package size="lg" />
                            </div>
                            <flux:heading size="lg" class="mb-1 text-zinc-900 dark:text-zinc-100">{{ __('general.no_products_found') }}</flux:heading>
                        </div>
                    @endforelse
                </div>
                <div class="mt-12">
                    {{ $this->products->links() }}
                </div>
            </flux:tab.panel>

            <flux:tab.panel name="articles">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($this->articles as $article)
                        <a
                            href="{{ route('post.view', ['slug' => $article->slug]) }}"
                            wire:navigate
                            wire:key="view-article-{{ $article->id }}"
                            class="group flex flex-col sm:flex-row gap-5 p-4 md:p-5 bg-white dark:bg-zinc-900 rounded-3xl shadow-sm hover:shadow-xl dark:hover:shadow-zinc-950/60 transition-all duration-300 border border-zinc-200/70 dark:border-zinc-800 hover:-translate-y-1"
                        >
                            <div class="w-full sm:w-44 h-44 flex-shrink-0 rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 relative">
                                @if($article->file_path)
                                    <img
                                        src="{{ Storage::url($article->file_path) }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                        alt="{{ $article->title }}"
                                    />
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-zinc-300 dark:text-zinc-600">
                                        <flux:icon.newspaper size="lg" />
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-col justify-between flex-grow">
                                <div>
                                    <div class="flex items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400 mb-2">
                                        <div class="flex items-center gap-1.5">
                                            <flux:icon.calendar size="xs" />
                                            <span>{{ jalali($article->created_at, 'Y/m/d') }}</span>
                                        </div>
                                    </div>
                                    <flux:heading size="lg" class="mb-2 line-clamp-2 text-zinc-900 dark:text-zinc-100 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors leading-snug">
                                        {{ $article->title }}
                                    </flux:heading>
                                    <flux:text class="line-clamp-2 text-sm text-zinc-600 dark:text-zinc-300 mb-3 leading-relaxed">
                                        {{ $article->excerpt ?? $article->summary }}
                                    </flux:text>
                                </div>
                                <div class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-600 dark:text-teal-400 mt-2">
                                    <span>{{ __('general.read_more') }}</span>
                                    <flux:icon.chevron-left size="xs" class="group-hover:-translate-x-1 transition-transform" />
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full py-20 flex flex-col items-center justify-center text-center">
                            <div class="h-16 w-16 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 dark:text-zinc-500 mb-4 shadow-sm">
                                <flux:icon.book-open size="lg" />
                            </div>
                            <flux:heading size="lg" class="mb-1 text-zinc-900 dark:text-zinc-100">{{ __('general.no_articles_found') }}</flux:heading>
                        </div>
                    @endforelse
                </div>
                <div class="mt-12">
                    {{ $this->articles->links() }}
                </div>
            </flux:tab.panel>
        </flux:tab.group>
    </div>
</div>
