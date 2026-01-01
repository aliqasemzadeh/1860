<flux:modal name="panel.shop.shipping.zone.edit.modal" class="md:w-[32rem]" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ __('app.edit_shipping_zone') }} : {{ isset($name) ? $name : '' }}
            </flux:heading>
            <flux:text class="mt-2">{{ __('app.edit_shipping_zone_description') }}</flux:text>
        </div>

        <form wire:submit="edit" method="post">
            <div class="pb-2 space-y-3">
                <flux:field>
                    <flux:label>{{ __('app.name') }}</flux:label>
                    <flux:input wire:model="name" type="text" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.countries') }}</flux:label>
                    <flux:textarea wire:model="countries" rows="2" />
                    <flux:text class="mt-1 text-xs text-gray-500">
                        {{ __('app.shipping_countries_help') }}
                    </flux:text>
                    <flux:error name="countries" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.states') }}</flux:label>
                    <flux:pillbox
                        multiple
                        searchable
                        wire:model="states"
                        placeholder="{{ __('app.select_provinces') }}"
                    >
                        @foreach($provinces as $provinceId => $provinceName)
                            <flux:pillbox.option value="{{ $provinceId }}">
                                {{ $provinceName }}
                            </flux:pillbox.option>
                        @endforeach
                    </flux:pillbox>
                    <flux:text class="mt-1 text-xs text-gray-500">
                        {{ __('app.shipping_states_help') }}
                    </flux:text>
                    <flux:error name="states" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.cities') }}</flux:label>
                    <flux:pillbox
                        multiple
                        searchable
                        wire:model="cities"
                        placeholder="{{ __('app.select_cities') }}"
                    >
                        @foreach($cityOptions as $provinceId => $provinceCities)
                            @foreach($provinceCities as $cityIndex => $cityName)
                                <flux:pillbox.option value="{{ $provinceId }}:{{ $cityIndex }}">
                                    {{ $cityName }} ({{ $provinces[$provinceId] ?? '' }})
                                </flux:pillbox.option>
                            @endforeach
                        @endforeach
                    </flux:pillbox>
                    <flux:text class="mt-1 text-xs text-gray-500">
                        {{ __('app.shipping_cities_help') }}
                    </flux:text>
                    <flux:error name="cities" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.areas') }}</flux:label>
                    <flux:textarea wire:model="areas" rows="3" />
                    <flux:text class="mt-1 text-xs text-gray-500">
                        {{ __('app.shipping_areas_help') }}
                    </flux:text>
                    <flux:error name="areas" />
                </flux:field>
            </div>

            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('app.update') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
