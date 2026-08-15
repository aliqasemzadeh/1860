<?php

use App\Models\Content\Box;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $id;
    public $slug;

    public function mount($id, $slug = null)
    {
        $this->id = $id;
        $this->slug = $slug;
    }

    #[Computed]
    public function box()
    {
        return Box::query()->findOrFail($this->id);
    }

    #[Computed]
    public function products()
    {
        return $this->box->products()->select('products.*')->withEffectivePrice()->paginate(12, pageName: 'products-page');
    }

    #[Computed]
    public function articles()
    {
        return $this->box->posts()->paginate(10, pageName: 'articles-page');
    }
}; ?>

<div>
    <x-slot name="title">
        {{ $this->box->title_fa }} - {{ config('app.name') }}
    </x-slot>

    @php
        $theme = $this->box->color_theme ?? [];
        $bgColor = $theme['bg'] ?? '#ffffff';
        $textColor = $theme['text'] ?? '#000000';
        $accentColor = $theme['accent'] ?? '#3b82f6';
    @endphp

    <div class="relative w-full h-64 md:h-96 rounded-b-[3rem] overflow-hidden mb-12 shadow-2xl">
        @if($this->box->getFirstMediaUrl('box_images'))
            <img
                src="{{ $this->box->getFirstMediaUrl('box_images') }}"
                class="w-full h-full object-cover"
                alt="{{ $this->box->title_fa }}"
            />
        @else
            <div class="w-full h-full transition-all duration-700" style="background: linear-gradient(135deg, {{ $bgColor }}, {{ $accentColor }});"></div>
        @endif
        <div class="absolute inset-0 bg-black/40 flex items-center justify-center text-center p-6">
            <div class="max-w-4xl">
                <flux:breadcrumbs class="mb-4 text-white/80">
                    <flux:breadcrumbs.item href="{{ route('home') }}">{{ __('general.home') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>{{ $this->box->title_fa }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <flux:heading size="2xl" class="text-white drop-shadow-lg">{{ $this->box->title_fa }}</flux:heading>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 2xl:px-0 mb-16">
        <flux:tabs variant="segmented">
            <flux:tabs.item name="products" icon="package" selected>{{ __('general.products') }}</flux:tabs.item>
            <flux:tabs.item name="articles" icon="book-open">{{ __('general.articles') }}</flux:tabs.item>

            <flux:tabs.panel name="products">
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
                            <flux:heading size="md" class="line-clamp-2 h-12 mb-4 group-hover:text-teal-600 transition-colors">
                                {{ $product->name }}
                            </flux:heading>
                            <div class="flex items-center justify-between border-t border-zinc-50 dark:border-zinc-800 pt-4">
                                @if($product->effective_price)
                                    <div class="text-teal-600 dark:text-teal-400 font-bold text-xl">
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
            </flux:tabs.panel>

            <flux:tabs.panel name="articles">
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
            </flux:tabs.panel>
        </flux:tabs>
    </div>
</div>
