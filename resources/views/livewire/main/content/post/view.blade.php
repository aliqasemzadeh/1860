<x-slot name="head">
    <x-seo :seo="$this->seo" />
</x-slot>

<div>
    @if ($this->post)
        <section class="py-8 antialiased md:py-12">
            <div class="mx-auto max-w-4xl px-4 2xl:px-0">
                <nav class="mb-6 flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                    <a href="{{ route('home') }}" class="transition-colors hover:text-zinc-900 dark:hover:text-zinc-100">
                        {{ __('general.home') }}
                    </a>
                    <span>/</span>
                    <a href="{{ route('post.index') }}" wire:navigate class="transition-colors hover:text-zinc-900 dark:hover:text-zinc-100">
                        {{ __('general.blog') }}
                    </a>
                    <span>/</span>
                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->post->title }}</span>
                </nav>

                @if ($this->post->featured_image_url)
                    <img
                        src="{{ $this->post->featured_image_url }}"
                        alt="{{ $this->post->title }}"
                        class="mb-6 h-64 w-full rounded-xl object-cover md:h-96"
                    />
                @endif

                <flux:heading size="xl" level="1" class="mb-3">{{ $this->post->title }}</flux:heading>

                <div class="mb-6 flex flex-wrap items-center gap-3 text-sm text-zinc-500 dark:text-zinc-400">
                    @if ($this->post->published_at)
                        <span>{{ jalali($this->post->published_at, 'Y/m/d') }}</span>
                    @endif

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

                @if ($this->post->summary)
                    <flux:text class="mb-6 text-lg text-zinc-600 dark:text-zinc-300">
                        {{ $this->post->summary }}
                    </flux:text>
                @endif

                <div class="prose prose-zinc dark:prose-invert max-w-none">
                    {!! $this->post->content !!}
                </div>

                @if ($this->post->products->isNotEmpty())
                    <div class="mt-12">
                        <flux:heading size="lg" class="mb-4">{{ __('general.related_products') }}</flux:heading>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($this->post->products as $product)
                                <a href="{{ $product->url }}" wire:navigate class="group block transition-opacity hover:opacity-90">
                                    @if ($product->file_path)
                                        <img
                                            src="{{ Storage::url($product->file_path) }}"
                                            alt="{{ $product->name }}"
                                            class="mb-3 h-40 w-full rounded-lg object-cover"
                                        />
                                    @endif
                                    <flux:heading size="sm">{{ $product->name }}</flux:heading>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif
</div>
