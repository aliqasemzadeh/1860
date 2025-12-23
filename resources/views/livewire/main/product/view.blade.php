<div>
    @if($this->product)
        <section class="py-8 antialiased md:py-12">
            <div class="mx-auto max-w-7xl px-4 2xl:px-0">
                {{-- Breadcrumb --}}
                <nav class="mb-6 flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                    <a href="{{ route('home') }}" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                        {{ __('app.dashboard') }}
                    </a>
                    <span>/</span>
                    @if($this->product->category)
                        <span class="text-zinc-900 dark:text-zinc-100">{{ $this->product->category->name }}</span>
                    @endif
                    <span>/</span>
                    <span class="text-zinc-900 dark:text-zinc-100 font-medium">{{ $this->product->name }}</span>
                </nav>

                {{-- Product Details --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                    {{-- Product Image --}}
                    <div class="flex flex-col">
                        <div class="relative aspect-square w-full overflow-hidden bg-zinc-100 dark:bg-zinc-800 rounded-2xl shadow-lg">
                            <img 
                                src="{{ Storage::url($this->product->file_path) }}" 
                                alt="{{ $this->product->name }}"
                                class="w-full h-full object-cover"
                            />
                            @if($this->product->sale_price && $this->product->price && $this->product->sale_price < $this->product->price)
                                <div class="absolute top-4 right-4 bg-red-500 text-white text-sm font-bold px-4 py-2 rounded-lg shadow-lg">
                                    {{ __('app.discount') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Product Info --}}
                    <div class="flex flex-col space-y-6">
                        {{-- Title --}}
                        <div>
                            <flux:heading size="xl" class="mb-3 text-zinc-900 dark:text-zinc-100">
                                {{ $this->product->name }}
                            </flux:heading>
                            @if($this->product->brand)
                                <flux:text class="text-zinc-600 dark:text-zinc-400 text-lg">
                                    {{ __('app.brand') }}: <span class="font-medium">{{ $this->product->brand->name }}</span>
                                </flux:text>
                            @endif
                        </div>

                        {{-- Price --}}
                        <div class="border-t border-b border-zinc-200 dark:border-zinc-700 py-6">
                            @if($this->product->price)
                                <div class="flex flex-col gap-2">
                                    @if($this->product->sale_price && $this->product->sale_price < $this->product->price)
                                        <div class="flex items-center gap-4">
                                            <div class="text-4xl font-bold text-green-600 dark:text-green-400">
                                                {{ number_format($this->product->sale_price, 0) }} {{ __('app.toman') }}
                                            </div>
                                            <div class="text-2xl text-zinc-400 dark:text-zinc-500 line-through">
                                                {{ number_format($this->product->price, 0) }} {{ __('app.toman') }}
                                            </div>
                                        </div>
                                        @php
                                            $discountPercent = round((($this->product->price - $this->product->sale_price) / $this->product->price) * 100);
                                        @endphp
                                        <div class="text-sm text-green-600 dark:text-green-400 font-medium">
                                            {{ $discountPercent }}% {{ __('app.discount') }}
                                        </div>
                                    @else
                                        <div class="text-4xl font-bold text-zinc-900 dark:text-zinc-100">
                                            {{ number_format($this->product->price, 0) }} {{ __('app.toman') }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-lg text-zinc-400 dark:text-zinc-500">
                                    {{ __('app.price_not_available') }}
                                </div>
                            @endif
                        </div>

                        {{-- Colors --}}
                        @if($this->product->colors->isNotEmpty())
                            <div>
                                <flux:heading size="sm" class="mb-3 text-zinc-900 dark:text-zinc-100">
                                    {{ __('app.product_colors') }}
                                </flux:heading>
                                <div class="flex flex-wrap gap-3">
                                    @foreach($this->product->colors as $color)
                                        <div 
                                            class="relative group cursor-pointer"
                                            title="{{ $color->name }}"
                                        >
                                            <div 
                                                class="w-12 h-12 rounded-full border-2 border-zinc-300 dark:border-zinc-600 group-hover:border-zinc-900 dark:group-hover:border-zinc-100 transition-colors shadow-md"
                                                style="background-color: {{ $color->hex }};"
                                            ></div>
                                            @if($color->hex === '#ffffff' || $color->hex === '#FFFFFF')
                                                <div class="absolute inset-0 rounded-full border-2 border-zinc-300 dark:border-zinc-600"></div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Warranties --}}
                        @if($this->product->warranties->isNotEmpty())
                            <div>
                                <flux:heading size="sm" class="mb-3 text-zinc-900 dark:text-zinc-100">
                                    {{ __('app.product_warranties') }}
                                </flux:heading>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($this->product->warranties as $warranty)
                                        <span class="px-4 py-2 bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rounded-lg text-sm font-medium">
                                            {{ $warranty->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Specifications --}}
                        <div class="bg-zinc-50 dark:bg-zinc-900 rounded-xl p-6">
                            <flux:heading size="sm" class="mb-4 text-zinc-900 dark:text-zinc-100">
                                {{ __('app.description') }}
                            </flux:heading>
                            @if($this->product->description)
                                <flux:text class="text-zinc-700 dark:text-zinc-300 leading-relaxed whitespace-pre-line">
                                    {{ $this->product->description }}
                                </flux:text>
                            @else
                                <flux:text class="text-zinc-400 dark:text-zinc-500 italic">
                                    {{ __('app.empty_description') }}
                                </flux:text>
                            @endif
                        </div>

                        {{-- Technical Details --}}
                        @if($this->product->weight || $this->product->x_dimension || $this->product->y_dimension || $this->product->z_dimension || $this->product->unit)
                            <div class="bg-zinc-50 dark:bg-zinc-900 rounded-xl p-6">
                                <flux:heading size="sm" class="mb-4 text-zinc-900 dark:text-zinc-100">
                                    {{ __('app.technical_specifications') }}
                                </flux:heading>
                                <div class="grid grid-cols-2 gap-4">
                                    @if($this->product->weight)
                                        <div>
                                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ __('app.weight') }}
                                            </flux:text>
                                            <div class="text-lg font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ number_format($this->product->weight, 0) }} {{ __('app.gram') }}
                                            </div>
                                        </div>
                                    @endif
                                    @if($this->product->x_dimension || $this->product->y_dimension || $this->product->z_dimension)
                                        <div>
                                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ __('app.dimensions') }}
                                            </flux:text>
                                            <div class="text-lg font-medium text-zinc-900 dark:text-zinc-100">
                                                @if($this->product->x_dimension)
                                                    {{ number_format($this->product->x_dimension, 0) }}
                                                @endif
                                                @if($this->product->y_dimension)
                                                    × {{ number_format($this->product->y_dimension, 0) }}
                                                @endif
                                                @if($this->product->z_dimension)
                                                    × {{ number_format($this->product->z_dimension, 0) }}
                                                @endif
                                                {{ __('app.centimeter') }}
                                            </div>
                                        </div>
                                    @endif
                                    @if($this->product->unit)
                                        <div>
                                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ __('app.unit') }}
                                            </flux:text>
                                            <div class="text-lg font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ $this->product->unit->name }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Pricing Options --}}
                        @if($this->product->prices->isNotEmpty() && ($this->product->colors->count() > 1 || $this->product->warranties->count() > 1))
                            <div>
                                <flux:heading size="sm" class="mb-4 text-zinc-900 dark:text-zinc-100">
                                    {{ __('app.pricing') }}
                                </flux:heading>
                                <div class="space-y-3 max-h-64 overflow-y-auto">
                                    @foreach($this->product->prices->where('quantity', '>', 0) as $price)
                                        <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3 mb-1">
                                                    @if($price->color)
                                                        <div 
                                                            class="w-6 h-6 rounded-full border border-zinc-300 dark:border-zinc-600"
                                                            style="background-color: {{ $price->color->hex }};"
                                                            title="{{ $price->color->name }}"
                                                        ></div>
                                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $price->color->name }}</span>
                                                    @endif
                                                    @if($price->warranty)
                                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">•</span>
                                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $price->warranty->name }}</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-zinc-500 dark:text-zinc-500">
                                                    {{ __('app.quantity') }}: {{ number_format($price->quantity, 0) }}
                                                </div>
                                            </div>
                                            <div class="text-left">
                                                @if($price->sale_price && $price->sale_price < $price->price)
                                                    <div class="text-lg font-bold text-green-600 dark:text-green-400">
                                                        {{ number_format($price->sale_price, 0) }} {{ __('app.toman') }}
                                                    </div>
                                                    <div class="text-sm text-zinc-400 dark:text-zinc-500 line-through">
                                                        {{ number_format($price->price, 0) }} {{ __('app.toman') }}
                                                    </div>
                                                @else
                                                    <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                                        {{ number_format($price->price, 0) }} {{ __('app.toman') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @else
        <section class="py-16 antialiased">
            <div class="mx-auto max-w-7xl px-4 2xl:px-0">
                <div class="flex flex-col items-center justify-center py-16">
                    <flux:heading size="lg" class="text-zinc-500 dark:text-zinc-400 mb-2">
                        {{ __('app.product_not_found') }}
                    </flux:heading>
                    <flux:text class="text-zinc-400 dark:text-zinc-500 mb-6">
                        {{ __('app.product_not_found_description') }}
                    </flux:text>
                    <flux:button href="{{ route('home') }}">
                        {{ __('app.return_to_home') }}
                    </flux:button>
                </div>
            </div>
        </section>
    @endif
</div>
