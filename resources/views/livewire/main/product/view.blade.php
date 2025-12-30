<x-slot name="title">
    {{ $this->product->name ?? __('app.product_not_found') }}
</x-slot>
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
                    @php
                        $allImages = $this->allImages;
                        $imageUrls = $allImages->map(fn($img) => Storage::url($img['file_path']))->values()->all();
                        $initialImage = $allImages->isNotEmpty() ? Storage::url($allImages->first()['file_path']) : Storage::url($this->product->file_path);
                    @endphp
                    <div class="flex flex-col gap-4" x-data="{
                        selectedIndex: 0,
                        loading: false,
                        images: @js($imageUrls),
                        currentImage: @js($initialImage),
                        selectImage(index) {
                            if (this.selectedIndex === index) return;
                            this.loading = true;
                            this.selectedIndex = index;
                            // Lazy load the image
                            const img = new Image();
                            img.onload = () => {
                                this.currentImage = this.images[index];
                                this.loading = false;
                            };
                            img.onerror = () => {
                                this.loading = false;
                            };
                            img.src = this.images[index];
                        }
                    }">
                        {{-- Main Image --}}
                        <div class="relative aspect-square w-full overflow-hidden bg-zinc-100 dark:bg-zinc-800 rounded-2xl shadow-lg">
                            <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 z-10">
                                <svg class="animate-spin h-8 w-8 text-zinc-600 dark:text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <img
                                x-bind:src="currentImage"
                                alt="{{ $this->product->name }}"
                                class="w-full h-full object-cover transition-opacity duration-300"
                                x-bind:class="{ 'opacity-50': loading }"
                            />
                            @if($this->product->sale_price && $this->product->price && $this->product->sale_price < $this->product->price)
                                <div class="absolute top-4 right-4 bg-red-500 text-white text-sm font-bold px-4 py-2 rounded-lg shadow-lg">
                                    {{ __('app.discount') }}
                                </div>
                            @endif
                        </div>

                        {{-- Thumbnail Images --}}
                        @if($allImages->count() > 1)
                            <div class="flex gap-2 overflow-x-auto pb-2">
                                @foreach($allImages as $index => $image)
                                    <button
                                        type="button"
                                        x-on:click="selectImage({{ $index }})"
                                        class="flex-shrink-0 relative aspect-square w-16 h-16 overflow-hidden bg-zinc-100 dark:bg-zinc-800 rounded-lg border-2 transition-all hover:scale-105 focus:outline-none focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100"
                                        x-bind:class="selectedIndex === {{ $index }} ? 'border-zinc-900 dark:border-zinc-100 ring-2 ring-zinc-900 dark:ring-zinc-100' : 'border-zinc-300 dark:border-zinc-600 hover:border-zinc-900 dark:hover:border-zinc-100'"
                                    >
                                        <img
                                            src="{{ Storage::url($image['file_path']) }}"
                                            alt="{{ $this->product->name }} - {{ $index + 1 }}"
                                            class="w-full h-full object-cover"
                                            loading="lazy"
                                        />
                                        <div 
                                            class="absolute inset-0 bg-zinc-900 dark:bg-zinc-100 bg-opacity-20 dark:bg-opacity-20"
                                            x-show="selectedIndex === {{ $index }}"
                                        ></div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
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
                        <div class="border-t border-b border-zinc-200 dark:border-zinc-700 py-6" wire:ignore.self>
                            @php
                                $selectedPrice = $this->selectedPrice;
                            @endphp
                            @if($selectedPrice)
                                <div class="flex flex-col gap-2">
                                    @if($selectedPrice->sale_price && $selectedPrice->sale_price < $selectedPrice->price)
                                        <div class="flex items-center gap-4">
                                            <div class="text-4xl font-bold text-green-600 dark:text-green-400">
                                                {{ number_format($selectedPrice->sale_price, 0) }} {{ __('app.toman') }}
                                            </div>
                                            <div class="text-2xl text-zinc-400 dark:text-zinc-500 line-through">
                                                {{ number_format($selectedPrice->price, 0) }} {{ __('app.toman') }}
                                            </div>
                                        </div>
                                        @php
                                            $discountPercent = round((($selectedPrice->price - $selectedPrice->sale_price) / $selectedPrice->price) * 100);
                                        @endphp
                                        <div class="text-sm text-green-600 dark:text-green-400 font-medium">
                                            {{ $discountPercent }}% {{ __('app.discount') }}
                                        </div>
                                    @else
                                        <div class="text-4xl font-bold text-zinc-900 dark:text-zinc-100">
                                            {{ number_format($selectedPrice->price, 0) }} {{ __('app.toman') }}
                                        </div>
                                    @endif
                                    @if($selectedPrice->quantity <= 0)
                                        <div class="text-sm text-red-600 dark:text-red-400 font-medium">
                                            {{ __('app.out_of_stock') }}
                                        </div>
                                    @elseif($selectedPrice->quantity < 10)
                                        <div class="text-sm text-orange-600 dark:text-orange-400 font-medium">
                                            {{ __('app.low_stock') }} ({{ number_format($selectedPrice->quantity, 0) }} {{ __('app.remaining') }})
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
                                        <button
                                            type="button"
                                            wire:click="selectColor({{ $color->id }})"
                                            class="relative group cursor-pointer focus:outline-none focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 rounded-full"
                                            title="{{ $color->name }}"
                                        >
                                            <div
                                                class="w-12 h-12 rounded-full border-2 transition-colors shadow-md {{ $this->selectedColorId == $color->id ? 'border-zinc-900 dark:border-zinc-100 ring-2 ring-zinc-900 dark:ring-zinc-100' : 'border-zinc-300 dark:border-zinc-600 group-hover:border-zinc-900 dark:group-hover:border-zinc-100' }}"
                                                style="background-color: {{ $color->hex }};"
                                            ></div>
                                            @if($color->hex === '#ffffff' || $color->hex === '#FFFFFF')
                                                <div class="absolute inset-0 rounded-full border-2 border-zinc-300 dark:border-zinc-600"></div>
                                            @endif
                                            @if($this->selectedColorId == $color->id)
                                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white dark:border-zinc-900 flex items-center justify-center">
                                                    <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                        </button>
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
                                        <button
                                            type="button"
                                            wire:click="selectWarranty({{ $warranty->id }})"
                                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 {{ $this->selectedWarrantyId == $warranty->id ? 'bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 ring-2 ring-zinc-900 dark:ring-zinc-100' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}"
                                        >
                                            {{ $warranty->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Quantity and Add to Cart --}}
                        @if($this->selectedPrice && $this->selectedPrice->quantity > 0)
                            <div class="space-y-4">
                                <div>
                                    <flux:heading size="sm" class="mb-3 text-zinc-900 dark:text-zinc-100">
                                        {{ __('app.quantity') }}
                                    </flux:heading>
                                    <div class="flex items-center gap-3">
                                        <button
                                            type="button"
                                            wire:click="decreaseQuantity"
                                            class="w-10 h-10 flex items-center justify-center border border-zinc-300 dark:border-zinc-600 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors focus:outline-none focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                        </button>
                                        <input
                                            type="number"
                                            wire:model.live="quantity"
                                            min="1"
                                            max="{{ $this->selectedPrice->quantity }}"
                                            class="w-20 text-center border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100"
                                        />
                                        <button
                                            type="button"
                                            wire:click="increaseQuantity"
                                            class="w-10 h-10 flex items-center justify-center border border-zinc-300 dark:border-zinc-600 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors focus:outline-none focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                        </button>
                                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ __('app.max_quantity') }}: {{ number_format($this->selectedPrice->quantity, 0) }}
                                        </flux:text>
                                    </div>
                                </div>
                                <flux:button
                                    wire:click="addToCart"
                                    variant="primary"
                                    icon="shopping-cart"
                                    class="w-full py-3 text-lg"
                                >
                                    {{ __('app.add_to_cart') }}
                                </flux:button>
                            </div>
                        @elseif($this->selectedPrice && $this->selectedPrice->quantity <= 0)
                            <flux:button
                                variant="ghost"
                                class="w-full py-3 text-lg"
                                disabled
                            >
                                {{ __('app.out_of_stock') }}
                            </flux:button>
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

                        {{-- Product Attributes --}}
                        @php
                            $attributeValues = $this->product->attributeValues->keyBy('attribute_id');
                            $attributesByGroup = collect();
                            if($this->product->category) {
                                $attributesByGroup = $this->product->category->attributes
                                    ->filter(function($attribute) use ($attributeValues) {
                                        return $attributeValues->has($attribute->id) && !empty($attributeValues[$attribute->id]->display_value);
                                    })
                                    ->groupBy('attributeGroup.name');
                            }
                        @endphp
                        @if($attributesByGroup->isNotEmpty())
                            <div class="bg-zinc-50 dark:bg-zinc-900 rounded-xl p-6">
                                <flux:heading size="sm" class="mb-4 text-zinc-900 dark:text-zinc-100">
                                    {{ __('app.technical_specifications') }}
                                </flux:heading>
                                <div class="space-y-4">
                                    @foreach($attributesByGroup as $groupName => $groupAttributes)
                                        <div>
                                            @if($groupName)
                                                <flux:heading size="xs" class="mb-2 text-zinc-700 dark:text-zinc-300">
                                                    {{ $groupName }}
                                                </flux:heading>
                                            @endif
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                @foreach($groupAttributes as $attribute)
                                                    @php
                                                        $value = $attributeValues[$attribute->id] ?? null;
                                                        $displayValue = $value?->display_value ?? '-';
                                                    @endphp
                                                    @if($displayValue !== '-')
                                                        <div>
                                                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                                                {{ $attribute->label }}
                                                            </flux:text>
                                                            <div class="text-base font-medium text-zinc-900 dark:text-zinc-100 mt-1">
                                                                @if($attribute->type === 'multiselect' && is_array($displayValue))
                                                                    {{ implode('، ', $displayValue) }}
                                                                @else
                                                                    {{ $displayValue }}
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
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
