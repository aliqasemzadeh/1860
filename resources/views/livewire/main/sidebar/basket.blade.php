<flux:modal name="main.sidebar.basket.modal" position="right" flyout>
    <div class="space-y-6 h-full flex flex-col">
        <div>
            <flux:heading size="lg">{{ __('app.shopping_cart') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.shopping_cart_description') }}</flux:text>
        </div>

        @if($this->cartItems && $this->cartItems->count() > 0)
            <div class="flex-1 overflow-y-auto space-y-4">
                @foreach($this->cartItems as $item)
                    @if($item->itemable)
                        <div class="flex gap-4 p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            {{-- Product Image --}}
                            @if(method_exists($item->itemable, 'file_path') && $item->itemable->file_path)
                                <div class="flex-shrink-0">
                                    <img
                                        src="{{ \Illuminate\Support\Facades\Storage::url($item->itemable->file_path) }}"
                                        alt="{{ $item->itemable->name ?? '' }}"
                                        class="w-20 h-20 object-cover rounded-lg"
                                    />
                                </div>
                            @endif

                            {{-- Product Info --}}
                            <div class="flex-1 min-w-0">
                                <flux:heading size="sm" class="mb-1 text-zinc-900 dark:text-zinc-100">
                                    {{ $item->itemable->name ?? __('app.product') }}
                                </flux:heading>

                                {{-- Options (Color, Warranty) --}}
                                @if($item->options)
                                    @php
                                        $options = is_string($item->options) ? json_decode($item->options, true) : $item->options;
                                    @endphp
                                    @if(isset($options['color']))
                                        <div class="flex items-center gap-2 mb-1">
                                            <div
                                                class="w-4 h-4 rounded-full border border-zinc-300 dark:border-zinc-600"
                                                style="background-color: {{ $options['color']['hex'] ?? '#000' }};"
                                            ></div>
                                            <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">
                                                {{ $options['color']['name'] ?? '' }}
                                            </flux:text>
                                        </div>
                                    @endif
                                    @if(isset($options['warranty']))
                                        <flux:text class="text-xs text-zinc-600 dark:text-zinc-400 mb-1">
                                            {{ __('app.warranty') }}: {{ $options['warranty']['name'] ?? '' }}
                                        </flux:text>
                                    @endif
                                @endif

                                {{-- Price --}}
                                @php
                                    $itemPrice = $item->itemable->getPrice();
                                    $options = is_string($item->options) ? json_decode($item->options, true) : $item->options;
                                    $priceId = $options['price_id'] ?? null;
                                    if ($priceId) {
                                        $priceRecord = \App\Models\Shop\ProductPrice::find($priceId);
                                        if ($priceRecord) {
                                            $itemPrice = $priceRecord->sale_price && $priceRecord->sale_price < $priceRecord->price
                                                ? $priceRecord->sale_price
                                                : $priceRecord->price;
                                        }
                                    }
                                @endphp
                                <div class="mt-2">
                                    <flux:text class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                        {{ number_format($itemPrice * $item->quantity, 0) }} {{ __('app.toman') }}
                                    </flux:text>
                                    @if($item->quantity > 1)
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-500">
                                            {{ number_format($itemPrice, 0) }} {{ __('app.toman') }} × {{ $item->quantity }}
                                        </flux:text>
                                    @endif
                                </div>
                            </div>

                            {{-- Quantity Controls --}}
                            <div class="flex flex-col items-end gap-2">
                                <button
                                    type="button"
                                    wire:click="removeItem({{ $item->id }})"
                                    class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors"
                                    title="{{ __('app.remove_item') }}"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        wire:click="decreaseQuantity({{ $item->id }})"
                                        class="w-8 h-8 flex items-center justify-center border border-zinc-300 dark:border-zinc-600 rounded hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                        </svg>
                                    </button>
                                    <span class="w-8 text-center text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $item->quantity }}
                                        </span>
                                    <button
                                        type="button"
                                        wire:click="increaseQuantity({{ $item->id }})"
                                        class="w-8 h-8 flex items-center justify-center border border-zinc-300 dark:border-zinc-600 rounded hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Total and Checkout --}}
            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4 space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="sm" class="text-zinc-900 dark:text-zinc-100">
                        {{ __('app.total') }}
                    </flux:heading>
                    <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                        {{ number_format($this->totalAmount, 0) }} {{ __('app.toman') }}
                    </flux:heading>
                </div>
                <flux:button type="button" variant="primary" class="w-full">
                    {{ __('app.complete_order') }}
                </flux:button>
            </div>
        @else
            <div class="flex-1 flex items-center justify-center">
                <div class="text-center">
                    <flux:icon.shopping-cart class="w-16 h-16 mx-auto text-zinc-400 dark:text-zinc-600 mb-4" />
                    <flux:text class="text-zinc-500 dark:text-zinc-400">
                        {{ __('app.cart_is_empty') }}
                    </flux:text>
                </div>
            </div>
        @endif
    </div>
</flux:modal>
