<div>
    <section class="py-8 antialiased md:py-12">
        <div class="mx-auto max-w-7xl px-4 2xl:px-0">
            <style>
                .glide__bullet--active {
                    background: #52525b;
                }
                .glide__arrow {
                    border-color: rgb(229 231 235);
                }
                .dark .glide__arrow {
                    border-color: rgb(63 63 70);
                }
            </style>
            <div
                x-data="{
                    init() {
                        new Glide(this.$refs.glide, {
                            type: 'carousel',
                            perView: 8,
                            gap: 16,
                            breakpoints: {
                                1023: {
                                    perView: 3,
                                    gap: 16,
                                },
                            },
                            autoplay: false,
                        }).mount()
                    },
                }"
            >
                <div x-ref="glide" class="glide block relative px-12">
                    <div class="glide__track" data-glide-el="track">
                        <ul class="glide__slides">
                            @foreach($this->categories as $category)
                                <li class="glide__slide">
                                    <flux:card size="sm"
                                               class="hover:bg-zinc-50 dark:hover:bg-zinc-700 flex flex-col items-center justify-center gap-2">
                                        <flux:icon name="{{ $category->icon }}" class="size-12"/>
                                        <flux:heading
                                            class="whitespace-nowrap overflow-hidden text-ellipsis">{{ $category->name }}</flux:heading>
                                    </flux:card>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="glide__arrows pointer-events-none absolute inset-0 flex items-center justify-between" data-glide-el="controls">
                        <!-- Previous Button -->
                        <button
                            class="glide__arrow glide__arrow--left pointer-events-auto disabled:opacity-50 rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-2 inline-flex items-center justify-center hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors"
                            data-glide-dir="<"
                        >
                            <span aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-gray-600 dark:text-gray-300">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                                </svg>
                            </span>
                            <span class="sr-only">Skip to previous slide page</span>
                        </button>

                        <!-- Next Button -->
                        <button
                            class="glide__arrow glide__arrow--right pointer-events-auto disabled:opacity-50 rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-2 inline-flex items-center justify-center hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors"
                            data-glide-dir=">"
                        >
                            <span aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-gray-600 dark:text-gray-300">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </span>
                            <span class="sr-only">Skip to next slide page</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-8 antialiased  md:py-12">
        <div class="mx-auto max-w-7xl px-4 2xl:px-0">
            <div class="grid grid-cols-2 lg:grid-cols-8 gap-4">
                @foreach($this->products as $product)
                    <flux:card size="sm"
                               class="hover:bg-zinc-50 dark:hover:bg-zinc-700 flex flex-col items-center justify-center gap-2">
                        <img src="{{ Storage::url($product->file_path) }}" alt="{{ $product->name }}"
                             class="size-24 object-cover shadow-sm dark:shadow-md"/>
                        <flux:heading
                            class="whitespace-nowrap overflow-hidden text-ellipsis">{{ $product->name }}</flux:heading>
                    </flux:card>
                @endforeach
            </div>
        </div>
    </section>
</div>
