@php
use Illuminate\Support\Facades\Storage;
@endphp
<div>
    @forelse($this->boxes as $box)
        @php
            $theme = $box->color_theme ?? [];
            $bgColor = $theme['bg'] ?? '#ffffff';
            $textColor = $theme['text'] ?? '#000000';
            $accentColor = $theme['accent'] ?? '#3b82f6';
            $logoUrl = $box->getFirstMediaUrl('box_images');
        @endphp
        <section
            class="py-8 my-8 rounded-3xl overflow-hidden transition-all duration-300 bg-[var(--box-bg)] text-[var(--box-text)] dark:bg-zinc-900 dark:text-zinc-100 border border-transparent dark:border-zinc-800 shadow-sm"
            style="--box-bg: {{ $bgColor }}; --box-text: {{ $textColor }};"
        >
            <div class="mx-auto max-w-7xl px-4 2xl:px-0">
                <div class="flex items-center justify-between mb-6">
                    <a
                        href="{{ route('content.box.view', ['id' => $box->id, 'slug' => $box->title_en]) }}"
                        wire:navigate
                        class="flex items-center gap-3 group/title"
                    >
                        @if ($logoUrl)
                            <img
                                src="{{ $logoUrl }}"
                                alt="{{ $box->title_fa }}"
                                class="h-10 w-10 md:h-12 md:w-12 object-contain rounded-xl bg-white/95 dark:bg-zinc-800/95 p-1.5 shadow-sm border border-zinc-200/50 dark:border-zinc-700/60 group-hover/title:scale-105 transition-transform"
                            />
                        @else
                            <div class="w-2 h-8 rounded-full" style="background-color: {{ $accentColor }};"></div>
                        @endif
                        <flux:heading size="xl" class="text-[var(--box-text)] dark:text-zinc-100 group-hover/title:underline font-bold">
                            {{ $box->title_fa }}
                        </flux:heading>
                    </a>
                    <div class="flex items-center gap-2">
                        <flux:carousel.controls name="box-{{ $box->id }}-carousel" class="text-[var(--box-text)] dark:text-zinc-100" />
                        <flux:button
                            href="{{ route('content.box.view', ['id' => $box->id, 'slug' => $box->title_en]) }}"
                            wire:navigate
                            variant="ghost"
                            icon-trailing="chevron-left"
                            class="text-[var(--box-text)] dark:text-zinc-100 hover:bg-black/5 dark:hover:bg-white/10"
                        >
                            {{ __('general.view_all') }}
                        </flux:button>
                    </div>
                </div>

                <flux:carousel name="box-{{ $box->id }}-carousel" class="-mx-4" :arrows="false" track:class="px-4 scroll-px-4">
                    @foreach($box->products as $product)
                        <flux:carousel.slide class="w-4/5 sm:w-1/2 md:w-1/3 lg:w-1/4 xl:w-1/5 pr-4">
                            <a
                                href="{{ $product->url }}"
                                wire:navigate
                                wire:key="box-{{ $box->id }}-product-{{ $product->id }}"
                                class="group hover:shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden flex flex-col h-full block rounded-2xl"
                            >
                                <flux:card class="h-full flex flex-col p-3 bg-white dark:bg-zinc-800/90 border border-zinc-200/80 dark:border-zinc-700/70">
                                    <div class="relative aspect-square w-full overflow-hidden bg-zinc-50 dark:bg-zinc-900 rounded-xl mb-3">
                                        @if($product->file_path)
                                            <img
                                                src="{{ Storage::url($product->file_path) }}"
                                                alt="{{ product_image_alt($product) }}"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
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

                                    <div class="flex flex-col flex-grow items-center text-center">
                                        <flux:heading
                                            size="sm"
                                            class="mb-3 line-clamp-2 min-h-[2.75rem] text-zinc-900 dark:text-zinc-100 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors leading-snug"
                                        >
                                            {{ $product->name }}
                                        </flux:heading>

                                        <div class="mt-auto pt-3 border-t border-zinc-100 dark:border-zinc-700/70 w-full">
                                            @if($product->price)
                                                <div class="flex items-center justify-center gap-2">
                                                    @if($product->sale_price && $product->sale_price < $product->price)
                                                        <div class="flex flex-col items-center">
                                                            <div class="text-base font-bold text-emerald-600 dark:text-emerald-400">
                                                                {{ number_format($product->sale_price, 0) }} {{ __('general.toman') }}
                                                            </div>
                                                            <div class="text-xs text-zinc-400 dark:text-zinc-500 line-through">
                                                                {{ number_format($product->price, 0) }} {{ __('general.toman') }}
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                                            {{ number_format($product->price, 0) }} {{ __('general.toman') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="text-xs text-zinc-400 dark:text-zinc-500 text-center">
                                                    {{ __('general.price_not_available') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </flux:card>
                            </a>
                        </flux:carousel.slide>
                    @endforeach
                </flux:carousel>
            </div>
        </section>
    @empty
    @endforelse
</div>
