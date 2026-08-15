<div>
    @foreach($this->boxes as $box)
        @php
            $theme = $box->color_theme ?? [];
            $bgColor = $theme['bg'] ?? '#ffffff';
            $textColor = $theme['text'] ?? '#000000';
            $accentColor = $theme['accent'] ?? '#3b82f6';
        @endphp
        <section
            class="py-8 my-8 rounded-2xl overflow-hidden transition-all duration-500"
            style="background-color: {{ $bgColor }}; color: {{ $textColor }};"
        >
            <div class="mx-auto max-w-7xl px-4 2xl:px-0">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-8 rounded-full" style="background-color: {{ $accentColor }};"></div>
                        <flux:heading size="xl" style="color: {{ $textColor }};">{{ $box->title_fa }}</flux:heading>
                    </div>
                    <flux:button
                        href="{{ route('content.box.view', ['id' => $box->id, 'slug' => $box->title_en]) }}"
                        wire:navigate
                        variant="ghost"
                        icon-trailing="chevron-left"
                        style="color: {{ $textColor }};"
                    >
                        {{ __('general.view_all') }}
                    </flux:button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    @foreach($box->products as $product)
                        <a
                            href="{{ $product->url }}"
                            wire:navigate
                            wire:key="box-{{ $box->id }}-product-{{ $product->id }}"
                            class="group relative bg-white/10 backdrop-blur-md rounded-xl p-2 border border-white/20 hover:border-white/40 transition-all duration-300"
                        >
                            <div class="aspect-square rounded-lg overflow-hidden mb-3 bg-white">
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
                            <flux:heading size="sm" class="line-clamp-2 h-10 text-center" style="color: {{ $textColor }};">
                                {{ $product->name }}
                            </flux:heading>

                            <div class="mt-2 text-center">
                                @if($product->effective_price)
                                    <div class="font-bold text-lg">
                                        {{ number_format($product->effective_price, 0) }}
                                        <span class="text-xs opacity-70">{{ __('general.toman') }}</span>
                                    </div>
                                @else
                                    <div class="text-sm opacity-50">{{ __('general.unavailable') }}</div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endforeach
</div>
