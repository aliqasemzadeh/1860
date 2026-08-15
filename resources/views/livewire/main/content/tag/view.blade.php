<x-slot name="head">
    <x-seo :seo="$this->seo" />
</x-slot>

<div>
    <div class="mx-auto max-w-7xl px-4 2xl:px-0 py-6 md:py-10">
        <flux:breadcrumbs class="mb-6">
            <flux:breadcrumbs.item href="{{ route('home') }}">{{ __('general.home') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('post.index') }}">{{ __('general.blog') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $this->tag->name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div class="relative mb-10 overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-50 via-white to-zinc-100 p-6 md:p-10 dark:from-zinc-900 dark:via-zinc-900/90 dark:to-zinc-950 border border-zinc-200/70 dark:border-zinc-800 shadow-sm">
            <div class="space-y-3 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200/80 dark:border-teal-800/60">
                    <flux:icon.tag size="xs" />
                    <span>{{ $this->tag->name }}</span>
                </div>
                <flux:heading size="2xl" level="1">
                    {{ __('general.posts_with_tag', ['tag' => $this->tag->name]) }}
                </flux:heading>
                <flux:subheading size="lg" class="text-zinc-600 dark:text-zinc-300">{{ __('general.posts_description') }}</flux:subheading>
            </div>
        </div>

        @if ($this->posts->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($this->posts as $post)
                    <article wire:key="tag-post-card-{{ $post->id }}" class="group relative flex flex-col rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 overflow-hidden shadow-sm hover:shadow-xl dark:hover:shadow-zinc-950/60 transition-all duration-300 hover:-translate-y-1.5">
                        <a href="{{ $post->url }}" wire:navigate class="block relative aspect-[16/10] overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                            @if ($post->featured_image_url)
                                <img
                                    src="{{ $post->featured_image_url }}"
                                    alt="{{ $post->title }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                />
                            @else
                                <div class="w-full h-full flex items-center justify-center text-zinc-300 dark:text-zinc-600">
                                    <flux:icon.newspaper size="xl" />
                                </div>
                            @endif
                        </a>

                        <div class="flex flex-col flex-grow p-6">
                            <div class="flex items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400 mb-3">
                                @if ($post->published_at)
                                    <div class="flex items-center gap-1.5">
                                        <flux:icon.calendar size="xs" />
                                        <span>{{ jalali($post->published_at, 'Y/m/d') }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center gap-1.5">
                                    <flux:icon.clock size="xs" />
                                    <span>
                                        @php
                                            $readingMinutes = max(2, (int) ceil(mb_strlen(strip_tags($post->summary ?? '')) / 150));
                                        @endphp
                                        {{ $readingMinutes }} {{ __('general.min_read') }}
                                    </span>
                                </div>
                            </div>

                            <a href="{{ $post->url }}" wire:navigate class="block">
                                <flux:heading size="lg" class="line-clamp-2 mb-3 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors leading-snug">
                                    {{ $post->title }}
                                </flux:heading>
                            </a>

                            @if ($post->summary)
                                <flux:text class="line-clamp-3 text-sm text-zinc-600 dark:text-zinc-300 mb-4 flex-grow leading-relaxed">
                                    {{ $post->summary }}
                                </flux:text>
                            @endif

                            <div class="mt-auto pt-4 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between">
                                <a
                                    href="{{ $post->url }}"
                                    wire:navigate
                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-600 dark:text-teal-400 hover:text-teal-700 dark:hover:text-teal-300 transition-colors"
                                >
                                    <span>{{ __('general.read_more') }}</span>
                                    <flux:icon.chevron-left size="xs" class="group-hover:-translate-x-1 transition-transform" />
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-12">
                {{ $this->posts->links() }}
            </div>
        @else
            <div class="py-20 flex flex-col items-center justify-center text-center">
                <div class="h-16 w-16 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 dark:text-zinc-500 mb-4 shadow-sm">
                    <flux:icon.book-open size="lg" />
                </div>
                <flux:heading size="lg" class="mb-2">{{ __('general.no_posts_found') }}</flux:heading>
            </div>
        @endif
    </div>
</div>
