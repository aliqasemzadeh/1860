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
        class="relative w-full h-64 md:h-80 rounded-b-[3rem] overflow-hidden mb-12 shadow-2xl"
        style="background: linear-gradient(135deg, {{ $bgColor }}, {{ $accentColor }});"
    >
        <div class="absolute inset-0 flex items-center justify-center text-center p-6">
            <div class="max-w-4xl flex flex-col items-center">
                <flux:breadcrumbs class="mb-4" style="color: {{ $textColor }}; opacity: 0.8;">
                    <flux:breadcrumbs.item href="{{ route('home') }}">{{ __('general.home') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>{{ $this->box->title_fa }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>

                @if ($logoUrl)
                    <img
                        src="{{ $logoUrl }}"
                        alt="{{ $this->box->title_fa }}"
                        class="h-16 w-16 md:h-24 md:w-24 object-contain rounded-2xl bg-white/90 p-2 shadow-lg mb-4"
                    />
                @endif

                <flux:heading size="2xl" style="color: {{ $textColor }};">{{ $this->box->title_fa }}</flux:heading>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 2xl:px-0 mb-16">
        <flux:tab.group>
            <flux:tabs variant="segmented">
                <flux:tab name="products" icon="package">{{ __('general.products') }}</flux:tab>
                <flux:tab name="articles" icon="book-open">{{ __('general.articles') }}</flux:tab>
            </flux:tabs>

            <flux:tab.panel name="products">
                <div class="mt-8 grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @forelse($this->products as $product)
                        <a
                            href="{{ $product->url }}"
                            wire:navigate
                            wire:key="view-product-{{ $product->id }}"
                            class="group bg-white dark:bg-zinc-900 rounded-2xl p-3 shadow-sm hover:shadow-xl transition-all duration-300 border border-zinc-100 dark:border-zinc-800"
                        >
                            <div class="aspect-square rounded-xl overflow-hidden mb-4 bg-zinc-50 dark:bg-zinc-800">
                                @if($product->file_path)
                                    <img
                                        src="{{ Storage::url($product->file_path) }}"
                                        alt="{{ $product->name }}"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                    />
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-zinc-300">
                                        <flux:icon.image size="lg" />
                                    </div>
                                @endif
                            </div>
                            <flux:heading size="md" class="line-clamp-2 h-12 mb-4 transition-colors group-hover:opacity-80">
                                {{ $product->name }}
                            </flux:heading>
                            <div class="flex items-center justify-between border-t border-zinc-50 dark:border-zinc-800 pt-4">
                                @if($product->effective_price)
                                    <div class="font-bold text-xl" style="color: {{ $accentColor }};">
                                        {{ number_format($product->effective_price, 0) }}
                                        <span class="text-xs font-normal opacity-70">{{ __('general.toman') }}</span>
                                    </div>
                                @else
                                    <div class="text-zinc-400 text-sm">{{ __('general.unavailable') }}</div>
                                @endif
                                <flux:button size="xs" variant="primary" square icon="plus" color="teal" />
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full py-20 flex flex-col items-center opacity-50">
                            <flux:icon.package size="xl" class="mb-4" />
                            <flux:text>{{ __('general.no_products_found') }}</flux:text>
                        </div>
                    @endforelse
                </div>
                <div class="mt-12">
                    {{ $this->products->links() }}
                </div>
            </flux:tab.panel>

            <flux:tab.panel name="articles">
                <div class="mt-8 space-y-6">
                    @forelse($this->articles as $article)
                        <a
                            href="{{ route('post.view', ['slug' => $article->slug]) }}"
                            wire:navigate
                            wire:key="view-article-{{ $article->id }}"
                            class="flex gap-6 p-4 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm hover:shadow-md transition-all border border-zinc-100 dark:border-zinc-800"
                        >
                            <div class="w-32 h-32 md:w-48 md:h-48 flex-shrink-0 rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                                @if($article->file_path)
                                    <img
                                        src="{{ Storage::url($article->file_path) }}"
                                        class="w-full h-full object-cover"
                                        alt="{{ $article->title }}"
                                    />
                                @endif
                            </div>
                            <div class="flex flex-col justify-center">
                                <flux:heading size="lg" class="mb-2">{{ $article->title }}</flux:heading>
                                <flux:text class="line-clamp-3 mb-4">{{ $article->excerpt ?? $article->summary }}</flux:text>
                                <div class="flex items-center gap-4 text-xs opacity-60">
                                    <div class="flex items-center gap-1"><flux:icon.calendar size="xs" /> {{ $article->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="py-20 flex flex-col items-center opacity-50">
                            <flux:icon.book-open size="xl" class="mb-4" />
                            <flux:text>{{ __('general.no_articles_found') }}</flux:text>
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
