<flux:modal name="service-center.repair.admission.modal" class="md:w-2/3" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.admission') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.admission_description') }}</flux:text>
        </div>

        <!-- Modal body -->
        <form wire:submit="admission" method="post" class="space-y-6">
            <!-- First row: Owner / Device info -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Owner column -->
                <div class="space-y-4">
                    <flux:heading size="sm">{{ __('app.owner_information') }}</flux:heading>

                    <flux:field>
                        <flux:label>{{ __('app.owner_name') }}</flux:label>
                        <flux:input wire:model="owner_name" type="text" size="sm" />
                        <flux:error name="owner_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.owner_mobile') }}</flux:label>
                        <flux:input wire:model="owner_mobile" type="text" size="sm" />
                        <flux:error name="owner_mobile" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.owner_email') }}</flux:label>
                        <flux:input wire:model="owner_email" type="email" size="sm" />
                        <flux:error name="owner_email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.owner_national_code') }}</flux:label>
                        <flux:input wire:model="owner_national_code" type="text" size="sm" />
                        <flux:error name="owner_national_code" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.owner_address') }}</flux:label>
                        <flux:textarea wire:model="owner_address" rows="3" size="sm" />
                        <flux:error name="owner_address" />
                    </flux:field>
                </div>

                <!-- Device column -->
                <div class="space-y-4">
                    <flux:heading size="sm">{{ __('app.device_information') }}</flux:heading>

                    <flux:field>
                        <flux:label>{{ __('app.device_type') }}</flux:label>
                        <flux:input wire:model="device_type" type="text" size="sm" />
                        <flux:error name="device_type" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.device_brand') }}</flux:label>
                        <flux:input wire:model="device_brand" type="text" size="sm" />
                        <flux:error name="device_brand" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.device_model') }}</flux:label>
                        <flux:input wire:model="device_model" type="text" size="sm" />
                        <flux:error name="device_model" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.device_serial_number') }}</flux:label>
                        <flux:input wire:model="device_serial_number" type="text" size="sm" />
                        <flux:error name="device_serial_number" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.warranty_type') }}</flux:label>
                        <flux:input wire:model="warranty_type" type="text" size="sm" />
                        <flux:error name="warranty_type" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.warranty_date') }}</flux:label>
                        <flux:input wire:model="warranty_date" type="date" size="sm" />
                        <flux:error name="warranty_date" />
                    </flux:field>
                </div>
            </div>

            <!-- Second row: Problem / Accessories / Description -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <flux:field>
                    <flux:label>{{ __('app.device_problem') }}</flux:label>
                    <flux:textarea wire:model="device_problem" rows="3" size="sm" />
                    <flux:error name="device_problem" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.device_accessories') }}</flux:label>
                    <flux:textarea wire:model="device_accessories" rows="3" size="sm" />
                    <flux:error name="device_accessories" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.device_description') }}</flux:label>
                    <flux:textarea wire:model="device_description" rows="3" size="sm" />
                    <flux:error name="device_description" />
                </flux:field>
            </div>

            <!-- Admission description (optional) -->
            <div>
                <flux:field>
                    <flux:label>{{ __('app.admission_description_label') }}</flux:label>
                    <flux:textarea wire:model="admission_description" rows="3" size="sm" />
                    <flux:error name="admission_description" />
                </flux:field>
            </div>

            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('app.admission') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
