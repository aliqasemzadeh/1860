<div>
    <section class="py-8 antialiased md:py-12">
        <div class="mx-auto max-w-7xl px-4 2xl:px-0">
            @if($this->products->isEmpty())
                <div class="flex flex-col items-center justify-center py-16">
                    <flux:heading size="lg" class="text-zinc-500 dark:text-zinc-400 mb-2">
                        {{ __('app.no_products_found') }}
                    </flux:heading>
                    <flux:text class="text-zinc-400 dark:text-zinc-500">
                        {{ __('app.no_products_found_description') }}
                    </flux:text>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
                    @foreach($this->products as $product)
                        <flux:card 
                            class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1 overflow-hidden flex flex-col h-full"
                        >
                            <div class="relative aspect-square w-full overflow-hidden bg-zinc-100 dark:bg-zinc-800 rounded-t-lg">
                                <img 
                                    src="{{ Storage::url($product->file_path) }}" 
                                    alt="{{ $product->name }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                                @if($product->sale_price && $product->price)
                                    <div class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                                        {{ __('app.discount') }}
                                    </div>
                                @endif
                            </div>
                            
                            <div class="p-4 flex flex-col flex-grow">
                                <flux:heading 
                                    size="sm" 
                                    class="mb-2 line-clamp-2 min-h-[3rem] text-right"
                                >
                                    {{ $product->name }}
                                </flux:heading>
                                
                                <div class="mt-auto pt-3 border-t border-zinc-200 dark:border-zinc-700">
                                    @if($product->price)
                                        <div class="flex items-center justify-between gap-2">
                                            @if($product->sale_price)
                                                <div class="flex flex-col items-end">
                                                    <div class="text-lg font-bold text-green-600 dark:text-green-400">
                                                        {{ number_format($product->sale_price, 0) }} {{ __('app.toman') }}
                                                    </div>
                                                    <div class="text-sm text-zinc-400 dark:text-zinc-500 line-through">
                                                        {{ number_format($product->price, 0) }} {{ __('app.toman') }}
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                                    {{ number_format($product->price, 0) }} {{ __('app.toman') }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-sm text-zinc-400 dark:text-zinc-500 text-right">
                                            {{ __('app.price_not_available') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </flux:card>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
