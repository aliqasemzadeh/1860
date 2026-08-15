<x-slot name="head">
    <x-seo :seo="$this->seo" />
</x-slot>

<div>
    <section class="py-8 antialiased md:py-12">
        <div class="mx-auto max-w-7xl px-4 2xl:px-0">
            <nav class="mb-6 flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                <a href="{{ route('home') }}" class="transition-colors hover:text-zinc-900 dark:hover:text-zinc-100">
                    {{ __('general.home') }}
                </a>
                <span>/</span>
                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->tag->name }}</span>
            </nav>

            <flux:heading size="xl" level="1" class="mb-2">
                {{ __('general.posts_with_tag', ['tag' => $this->tag->name]) }}
            </flux:heading>
            <flux:subheading class="mb-8">{{ __('general.posts_description') }}</flux:subheading>

            @if ($this->posts->isNotEmpty())
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($this->posts as $post)
                        <a href="{{ $post->url }}" wire:navigate class="group block space-y-3 transition-opacity hover:opacity-90">
                            @if ($post->featured_image_url)
                                <img
                                    src="{{ $post->featured_image_url }}"
                                    alt="{{ $post->title }}"
                                    class="h-48 w-full rounded-xl object-cover"
                                />
                            @endif
                            <flux:heading size="md">{{ $post->title }}</flux:heading>
                            @if ($post->published_at)
                                <flux:text class="text-sm text-zinc-500">{{ jalali($post->published_at, 'Y/m/d') }}</flux:text>
                            @endif
                            @if ($post->summary)
                                <flux:text class="line-clamp-3 text-zinc-600 dark:text-zinc-300">{{ $post->summary }}</flux:text>
                            @endif
                        </a>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $this->posts->links() }}
                </div>
            @else
                <flux:text class="text-zinc-500">{{ __('general.no_posts_found') }}</flux:text>
            @endif
        </div>
    </section>
</div>
