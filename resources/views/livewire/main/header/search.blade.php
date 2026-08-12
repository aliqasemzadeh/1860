<div>
    <flux:modal.trigger name="search" shortcut="cmd.f">
        <flux:input as="button" placeholder="{{ __('app.search') }}" icon="magnifying-glass" kbd="⌘K" />
    </flux:modal.trigger>
    <flux:modal name="search" variant="bare" class="w-full max-w-[30rem] my-[12vh] max-h-screen overflow-y-hidden">
        <flux:command class="border-none shadow-lg inline-flex flex-col max-h-[76vh]">
            <flux:command.input wire:model.live.debounce.150ms="query" placeholder="{{ __('app.search_placeholder') }}" closable />
            <flux:command.items>
                @forelse($this->products as $product)
                    <flux:command.item 
                        wire:key="product-{{ $product->id }}"
                        href="{{ $product->url }}"
                        wire:navigate
                    >
                        <div class="flex items-center gap-3 w-full">
                            <flux:avatar 
                                size="sm" 
                                src="{{ $product->file_path ? Storage::url($product->file_path) : '' }}"
                                alt="{{ $product->name }}"
                            />
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="font-medium text-sm truncate">
                                        {{ $product->name }}
                                    </div>
                                    @if($product->price)
                                        <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                            {{ number_format($product->sale_price && $product->sale_price < $product->price ? $product->sale_price : $product->price, 0) }} {{ __('app.toman') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between gap-2 mt-1">
                                    @if($product->category)
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
                                            {{ $product->category->name }}
                                        </div>
                                    @endif
                                    @php
                                        $defaultPrice = $product->default_price;
                                        $isAvailable = ($defaultPrice['available'] ?? false) === true;
                                    @endphp
                                    <div class="text-xs whitespace-nowrap">
                                        @if($isAvailable)
                                            <span class="text-green-600 dark:text-green-400">{{ __('app.remaining') }}</span>
                                        @else
                                            <span class="text-red-600 dark:text-red-400">{{ __('app.out_of_stock') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </flux:command.item>
                @empty
                    @if(!empty($this->query))
                        <flux:command.item disabled>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400 text-center py-2">
                                {{ __('app.no_results') }}
                            </div>
                        </flux:command.item>
                    @endif
                @endforelse
            </flux:command.items>
        </flux:command>
    </flux:modal>
</div>
