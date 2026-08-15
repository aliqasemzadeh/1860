<flux:modal name="panel.shop.shipping.zone.create.modal" class="md:w-[32rem]" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.create_shipping_zone') }}</flux:heading>
            <flux:text class="mt-2">{{ __('general.create_shipping_zone_description') }}</flux:text>
        </div>

        <form wire:submit="create" method="post">
            <div class="pb-2 space-y-3">
                <flux:field>
                    <flux:label>{{ __('general.name') }}</flux:label>
                    <flux:input wire:model="name" type="text" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.countries') }}</flux:label>
                    <flux:textarea wire:model="countries" rows="2" />
                    <flux:text class="mt-1 text-xs text-gray-500">
                        {{ __('general.shipping_countries_help') }}
                    </flux:text>
                    <flux:error name="countries" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.states') }}</flux:label>
                    <flux:pillbox
                        multiple
                        searchable
                        wire:model.live="states"
                        placeholder="{{ __('general.select_provinces') }}"
                    >
                        @foreach($provinces as $provinceId => $provinceName)
                            <flux:pillbox.option value="{{ $provinceId }}">
                                {{ $provinceName }}
                            </flux:pillbox.option>
                        @endforeach
                    </flux:pillbox>
                    <flux:text class="mt-1 text-xs text-gray-500">
                        {{ __('general.shipping_states_help') }}
                    </flux:text>
                    <flux:error name="states" />
                </flux:field>

                @if(!empty($states))
                <flux:field>
                    <flux:label>{{ __('general.cities') }}</flux:label>
                    <flux:pillbox
                        multiple
                        searchable
                        wire:model="cities"
                        placeholder="{{ __('general.select_cities') }}"
                    >
                        @foreach($cityOptions as $cityKey => $cityName)
                            <flux:pillbox.option value="{{ $cityKey }}">
                                {{ $cityName }}
                            </flux:pillbox.option>
                        @endforeach
                    </flux:pillbox>
                    <flux:text class="mt-1 text-xs text-gray-500">
                        {{ __('general.shipping_cities_help') }}
                    </flux:text>
                    <flux:error name="cities" />
                </flux:field>
                @endif

                <flux:field>
                    <flux:label>{{ __('general.areas') }}</flux:label>
                    <flux:textarea wire:model="areas" rows="3" />
                    <flux:text class="mt-1 text-xs text-gray-500">
                        {{ __('general.shipping_areas_help') }}
                    </flux:text>
                    <flux:error name="areas" />
                </flux:field>
            </div>

            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('general.create') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
