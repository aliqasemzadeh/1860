<div>
    <flux:modal name="panel.user.address.create.modal" class="md:w-96" flyout position="right">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('app.create_address') }}</flux:heading>
                <flux:text class="mt-2">{{ __('app.create_address_description') }}</flux:text>
            </div>

            <form wire:submit="create" method="post">
                <div class="pb-2 space-y-3">
                    <flux:field>
                        <flux:label>{{ __('app.address_name') }}</flux:label>
                        <flux:input wire:model="form.name" type="text" />
                        <flux:error name="form.name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.province') }}</flux:label>
                        <flux:select wire:model.live="form.province_id" variant="combobox">
                            <flux:select.option value="">{{ __('app.select_province') }}</flux:select.option>
                            @foreach ($this->provinces as $id => $province)
                                <flux:select.option value="{{ $id }}">{{ $province }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="form.province_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.city') }}</flux:label>
                        <flux:select wire:model="form.city_id" variant="combobox">
                            <flux:select.option value="">{{ __('app.select_city') }}</flux:select.option>
                            @foreach ($this->cities as $cityKey => $cityName)
                                <flux:select.option value="{{ $cityKey }}">{{ $cityName }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="form.city_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.address') }}</flux:label>
                        <flux:textarea wire:model="form.address" rows="3" />
                        <flux:error name="form.address" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.postal_code') }}</flux:label>
                        <flux:input wire:model="form.postal_code" type="text" />
                        <flux:error name="form.postal_code" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.emergency_contact') }}</flux:label>
                        <flux:input wire:model="form.emergency_contact" type="text" />
                        <flux:error name="form.emergency_contact" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.default_address') }}</flux:label>
                        <flux:switch wire:model="form.is_default" />
                        <flux:error name="form.is_default" />
                    </flux:field>
                </div>

                <flux:button type="submit" class="w-full" variant="primary" color="green">
                    {{ __('app.save') }}
                </flux:button>
            </form>
        </div>
    </flux:modal>
</div>
