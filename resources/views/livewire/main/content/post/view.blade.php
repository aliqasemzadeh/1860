<x-slot name="head">
    <x-seo :seo="$this->seo" />
</x-slot>

<div>
    @if ($this->post)
        <div class="mx-auto max-w-4xl px-4 2xl:px-0 py-6 md:py-10">
            <flux:breadcrumbs class="mb-6">
                <flux:breadcrumbs.item href="{{ route('home') }}">{{ __('general.home') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('post.index') }}">{{ __('general.blog') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->post->title }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <article class="space-y-8">
                <header class="space-y-4">
                    @if ($this->post->tags->isNotEmpty())
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach ($this->post->tags->take(3) as $tag)
                                <a
                                    href="{{ route('tag.view', ['id' => $tag->id, 'slug' => $tag->slug]) }}"
                                    wire:navigate
                                    class="transition-opacity hover:opacity-80"
                                >
                                    <flux:badge size="sm" color="zinc">{{ $tag->name }}</flux:badge>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <flux:heading size="2xl" level="1" class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 leading-snug">
                        {{ $this->post->title }}
                    </flux:heading>

                    <div class="flex flex-wrap items-center justify-between gap-4 py-4 border-y border-zinc-200/80 dark:border-zinc-800 text-sm text-zinc-600 dark:text-zinc-400">
                        <div class="flex flex-wrap items-center gap-4">
                            @if ($this->post->published_at)
                                <div class="flex items-center gap-1.5 font-medium">
                                    <flux:icon.calendar size="xs" class="text-zinc-400 dark:text-zinc-500" />
                                    <span>{{ jalali($this->post->published_at, 'Y/m/d') }}</span>
                                </div>
                            @endif
                            <div class="flex items-center gap-1.5 font-medium">
                                <flux:icon.clock size="xs" class="text-zinc-400 dark:text-zinc-500" />
                                <span>
                                    @php
                                        $readingMinutes = max(2, (int) ceil(mb_strlen(strip_tags(($this->post->summary ?? '') . ' ' . ($this->post->content ?? ''))) / 500));
                                    @endphp
                                    {{ $readingMinutes }} {{ __('general.min_read') }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <flux:button
                                size="sm"
                                variant="subtle"
                                icon="share-2"
                                x-data
                                @click="navigator.clipboard.writeText(window.location.href); Flux.toast('{{ __('general.link_copied') }}');"
                            >
                                {{ __('general.copy_link') }}
                            </flux:button>
                        </div>
                    </div>
                </header>

                @if ($this->post->featured_image_url)
                    <div class="relative overflow-hidden rounded-3xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200/70 dark:border-zinc-800 shadow-sm">
                        <img
                            src="{{ $this->post->featured_image_url }}"
                            alt="{{ $this->post->title }}"
                            class="w-full max-h-[500px] object-cover"
                        />
                    </div>
                @endif

                @if ($this->post->summary)
                    <div class="p-6 md:p-8 rounded-3xl bg-zinc-50 dark:bg-zinc-900/90 border-r-4 border-r-teal-500 border border-zinc-200/70 dark:border-zinc-800 shadow-sm">
                        <flux:text class="text-base md:text-lg font-medium leading-relaxed text-zinc-700 dark:text-zinc-200">
                            {{ $this->post->summary }}
                        </flux:text>
                    </div>
                @endif

                <div class="prose prose-lg dark:prose-invert prose-headings:font-bold prose-headings:tracking-tight prose-headings:text-zinc-900 dark:prose-headings:text-zinc-100 prose-a:text-teal-600 dark:prose-a:text-teal-400 prose-img:rounded-2xl prose-img:shadow-md max-w-none leading-loose text-zinc-700 dark:text-zinc-300">
                    {!! $this->post->content !!}
                </div>

                @if ($this->post->tags->isNotEmpty())
                    <div class="pt-8 border-t border-zinc-200/80 dark:border-zinc-800">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400 flex items-center gap-1.5 ml-2">
                                <flux:icon.tag size="xs" />
                                {{ __('general.tags') }}:
                            </span>
                            @foreach ($this->post->tags as $tag)
                                <a
                                    href="{{ route('tag.view', ['id' => $tag->id, 'slug' => $tag->slug]) }}"
                                    wire:navigate
                                    class="transition-opacity hover:opacity-80"
                                >
                                    <flux:badge size="sm" color="zinc">{{ $tag->name }}</flux:badge>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </article>

            @if ($this->post->products->isNotEmpty())
                <div class="mt-16 pt-10 border-t border-zinc-200/80 dark:border-zinc-800">
                    <div class="flex items-center justify-between mb-8">
                        <flux:heading size="xl" class="flex items-center gap-2">
                            <flux:icon.package size="sm" class="text-teal-600 dark:text-teal-400" />
                            {{ __('general.related_products') }}
                        </flux:heading>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach ($this->post->products as $product)
                            <a
                                href="{{ $product->url }}"
                                wire:navigate
                                wire:key="related-product-{{ $product->id }}"
                                class="group bg-white dark:bg-zinc-900 rounded-2xl p-3 shadow-sm hover:shadow-xl transition-all duration-300 border border-zinc-100 dark:border-zinc-800 flex flex-col h-full hover:-translate-y-1"
                            >
                                <div class="aspect-square rounded-xl overflow-hidden mb-4 bg-zinc-50 dark:bg-zinc-800 relative">
                                    @if($product->file_path)
                                        <img
                                            src="{{ Storage::url($product->file_path) }}"
                                            alt="{{ $product->name }}"
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
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

                                <flux:heading size="md" class="line-clamp-2 mb-4 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">
                                    {{ $product->name }}
                                </flux:heading>

                                <div class="mt-auto flex items-center justify-between border-t border-zinc-100 dark:border-zinc-800 pt-3">
                                    @if($product->effective_price || $product->price)
                                        <div class="font-bold text-base text-zinc-900 dark:text-zinc-100">
                                            {{ number_format($product->effective_price ?? $product->price, 0) }}
                                            <span class="text-xs font-normal opacity-70 text-zinc-500 dark:text-zinc-400">{{ __('general.toman') }}</span>
                                        </div>
                                    @else
                                        <div class="text-zinc-400 dark:text-zinc-500 text-xs">{{ __('general.unavailable') }}</div>
                                    @endif
                                    <flux:button size="xs" variant="primary" square icon="chevron-left" color="teal" />
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
