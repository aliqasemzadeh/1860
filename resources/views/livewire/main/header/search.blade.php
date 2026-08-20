<div
    x-data="{
        cookieName: 'search_history',
        loadCookie() {
            const match = document.cookie.match(new RegExp('(?:^|; )' + this.cookieName + '=([^;]*)'));

            if (! match) {
                return [];
            }

            try {
                return JSON.parse(decodeURIComponent(match[1])) || [];
            } catch (error) {
                return [];
            }
        },
        saveCookie(term) {
            term = (term || '').trim();

            if (term.length < 2) {
                return;
            }

            const history = [term, ...this.loadCookie().filter((item) => item !== term)].slice(0, 10);

            document.cookie = `${this.cookieName}=${encodeURIComponent(JSON.stringify(history))};path=/;max-age=${60 * 60 * 24 * 30};SameSite=Lax`;

            $wire.syncSearchHistory(history);
        },
        clearCookie() {
            document.cookie = `${this.cookieName}=;path=/;max-age=0;SameSite=Lax`;
            $wire.clearSearchHistory();
        },
        openSearch() {
            $wire.syncSearchHistory(this.loadCookie());
            $wire.set('query', '');
        },
    }"
    x-on:modal-show.document="if ($event.detail?.name === 'search') openSearch()"
>
    <flux:modal.trigger name="search" shortcut="cmd.f">
        <flux:input as="button" placeholder="{{ __('general.search') }}" icon="magnifying-glass" kbd="⌘K" />
    </flux:modal.trigger>
    <flux:modal name="search" variant="bare" wire:close="handleSearchClose" class="w-full max-w-[30rem] my-[12vh] max-h-screen overflow-y-hidden">
        <flux:command class="border-none shadow-lg inline-flex flex-col max-h-[76vh]" filter="false">
            <flux:command.input
                wire:model.live.debounce.150ms="query"
                placeholder="{{ __('general.search_placeholder') }}"
                closable
                x-on:keydown.enter.prevent="saveCookie($wire.query); $wire.goToSearchPage()"
            />
            <flux:command.items>
                @if(empty($this->query) && count($recentSearches) > 0)
                    <div class="flex items-center justify-between px-2 py-1.5">
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('general.recent_searches') }}</span>
                        <button
                            type="button"
                            x-on:click="clearCookie()"
                            class="text-xs text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                        >
                            {{ __('general.clear_search_history') }}
                        </button>
                    </div>
                    @foreach($recentSearches as $term)
                        <flux:command.item
                            wire:key="history-{{ md5($term) }}"
                            wire:click="selectHistory(@js($term))"
                            icon="clock"
                        >
                            {{ $term }}
                        </flux:command.item>
                    @endforeach
                @else
                @forelse($this->products as $product)
                    <flux:command.item
                        wire:key="product-{{ $product->id }}"
                        wire:click="goToProduct({{ $product->id }})"
                        x-on:click="saveCookie($wire.query)"
                    >
                        <div class="flex items-center gap-3 w-full">
                            <flux:avatar
                                size="sm"
                                src="{{ $product->file_path ? Storage::url($product->file_path) : '' }}"
                                alt="{{ product_image_alt($product) }}"
                            />
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="font-medium text-sm truncate">
                                        {{ $product->name }}
                                    </div>
                                    @if($product->price)
                                        <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                            {{ number_format($product->sale_price && $product->sale_price < $product->price ? $product->sale_price : $product->price, 0) }} {{ __('general.toman') }}
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
                                            <span class="text-green-600 dark:text-green-400">{{ __('general.remaining') }}</span>
                                        @else
                                            <span class="text-red-600 dark:text-red-400">{{ __('general.out_of_stock') }}</span>
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
                                {{ __('general.no_results') }}
                            </div>
                        </flux:command.item>
                    @endif
                @endforelse
                    @if(mb_strlen(trim($this->query)) >= 2)
                        <flux:command.item
                            wire:click="goToSearchPage"
                            x-on:click="saveCookie($wire.query)"
                            icon="arrow-left"
                        >
                            {{ __('general.view_all_results') }}
                        </flux:command.item>
                    @endif
                @endif
            </flux:command.items>
        </flux:command>
    </flux:modal>
</div>
