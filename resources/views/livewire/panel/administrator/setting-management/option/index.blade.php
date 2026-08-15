<x-slot name="title">
    {{ __('general.options') }}
</x-slot>

<div>
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('general.options') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('general.options_description') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="space-y-6">
        <flux:tab.group>
            <flux:tabs wire:model="tab">
                <flux:tab name="general">{{ __('general.general_settings') }}</flux:tab>
                <flux:tab name="contact">{{ __('general.contact_settings') }}</flux:tab>
                <flux:tab name="social">{{ __('general.social_networks') }}</flux:tab>
                <flux:tab name="sms">{{ __('general.sms_settings') }}</flux:tab>
                <flux:tab name="maintenance">{{ __('general.maintenance_mode') }}</flux:tab>
            </flux:tabs>

            <flux:tab.panel name="general">
                <flux:card>
                    <form wire:submit="saveGeneral" class="space-y-4">
                        <flux:heading size="lg">{{ __('general.general_settings') }}</flux:heading>
                        <flux:text>{{ __('general.general_settings_description') }}</flux:text>
                        <flux:separator variant="subtle" />

                        <flux:field>
                            <flux:label>{{ __('general.site_title') }}</flux:label>
                            <flux:input wire:model="generalForm.title" />
                            <flux:error name="generalForm.title" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('general.site_description') }}</flux:label>
                            <flux:textarea wire:model="generalForm.description" rows="3" />
                            <flux:error name="generalForm.description" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('general.site_keywords') }}</flux:label>
                            <flux:input wire:model="generalForm.keywords" />
                            <flux:text class="mt-1 text-xs">{{ __('general.site_keywords_help') }}</flux:text>
                            <flux:error name="generalForm.keywords" />
                        </flux:field>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                @if ($generalForm->logo_path)
                                    <img
                                        src="{{ Storage::disk('public')->url($generalForm->logo_path) }}"
                                        alt="{{ __('general.site_logo') }}"
                                        class="mb-2 h-16 object-contain"
                                    />
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button
                                            size="xs"
                                            variant="primary"
                                            color="red"
                                            icon="trash"
                                            icon:variant="outline"
                                            wire:click="removeLogo"
                                            wire:confirm="{{ __('general.are_you_sure') }}"
                                        />
                                    </flux:tooltip>
                                @endif
                                <flux:file-upload wire:model="generalForm.logo" label="{{ __('general.site_logo') }}">
                                    <flux:file-upload.dropzone
                                        heading="{{ __('general.site_logo_upload') }}"
                                        text="PNG, JPG, WEBP, SVG - 2MB"
                                        with-progress
                                        inline
                                    />
                                </flux:file-upload>
                                <flux:error name="generalForm.logo" />
                            </div>

                            <div class="space-y-2">
                                @if ($generalForm->favicon_path)
                                    <img
                                        src="{{ Storage::disk('public')->url($generalForm->favicon_path) }}"
                                        alt="{{ __('general.site_favicon') }}"
                                        class="mb-2 h-10 object-contain"
                                    />
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button
                                            size="xs"
                                            variant="primary"
                                            color="red"
                                            icon="trash"
                                            icon:variant="outline"
                                            wire:click="removeFavicon"
                                            wire:confirm="{{ __('general.are_you_sure') }}"
                                        />
                                    </flux:tooltip>
                                @endif
                                <flux:file-upload wire:model="generalForm.favicon" label="{{ __('general.site_favicon') }}">
                                    <flux:file-upload.dropzone
                                        heading="{{ __('general.site_favicon_upload') }}"
                                        text="ICO, PNG, SVG - 512KB"
                                        with-progress
                                        inline
                                    />
                                </flux:file-upload>
                                <flux:error name="generalForm.favicon" />
                            </div>
                        </div>

                        <flux:button type="submit" class="w-full" variant="primary" color="teal">
                            {{ __('general.save') }}
                        </flux:button>
                    </form>
                </flux:card>
            </flux:tab.panel>

            <flux:tab.panel name="contact">
                <flux:card>
                    <form wire:submit="saveContact" class="space-y-4">
                        <flux:heading size="lg">{{ __('general.contact_settings') }}</flux:heading>
                        <flux:text>{{ __('general.contact_settings_description') }}</flux:text>
                        <flux:separator variant="subtle" />

                        <flux:field>
                            <flux:label>{{ __('general.address') }}</flux:label>
                            <flux:textarea wire:model="contactForm.address" rows="2" />
                            <flux:error name="contactForm.address" />
                        </flux:field>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <flux:field>
                                <flux:label>{{ __('general.mobile') }}</flux:label>
                                <flux:input wire:model="contactForm.mobile" dir="ltr" />
                                <flux:error name="contactForm.mobile" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('general.landline') }}</flux:label>
                                <flux:input wire:model="contactForm.phone" dir="ltr" />
                                <flux:error name="contactForm.phone" />
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label>{{ __('general.email') }}</flux:label>
                            <flux:input wire:model="contactForm.email" type="email" dir="ltr" />
                            <flux:error name="contactForm.email" />
                        </flux:field>

                        <flux:button type="submit" class="w-full" variant="primary" color="teal">
                            {{ __('general.save') }}
                        </flux:button>
                    </form>
                </flux:card>
            </flux:tab.panel>

            <flux:tab.panel name="social">
                <flux:card>
                    <form wire:submit="saveSocial" class="space-y-4">
                        <flux:heading size="lg">{{ __('general.social_networks') }}</flux:heading>
                        <flux:text>{{ __('general.social_networks_description') }}</flux:text>
                        <flux:separator variant="subtle" />

                        <flux:subheading>{{ __('general.social_iranian') }}</flux:subheading>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            @foreach ($this->networks as $network)
                                @continue(! $network->isIranian())
                                <flux:field>
                                    <flux:label>{{ $network->label() }}</flux:label>
                                    <flux:input
                                        wire:model="socialForm.links.{{ $network->value }}"
                                        icon="{{ $network->icon() }}"
                                        dir="ltr"
                                        placeholder="https://"
                                    />
                                    <flux:error name="socialForm.links.{{ $network->value }}" />
                                </flux:field>
                            @endforeach
                        </div>

                        <flux:separator variant="subtle" />

                        <flux:subheading>{{ __('general.social_foreign') }}</flux:subheading>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            @foreach ($this->networks as $network)
                                @continue($network->isIranian())
                                <flux:field>
                                    <flux:label>{{ $network->label() }}</flux:label>
                                    <flux:input
                                        wire:model="socialForm.links.{{ $network->value }}"
                                        icon="{{ $network->icon() }}"
                                        dir="ltr"
                                        placeholder="https://"
                                    />
                                    <flux:error name="socialForm.links.{{ $network->value }}" />
                                </flux:field>
                            @endforeach
                        </div>

                        <flux:button type="submit" class="w-full" variant="primary" color="teal">
                            {{ __('general.save') }}
                        </flux:button>
                    </form>
                </flux:card>
            </flux:tab.panel>

            <flux:tab.panel name="sms">
                <flux:card>
                    <form wire:submit="saveSms" class="space-y-4">
                        <flux:heading size="lg">{{ __('general.sms_settings') }}</flux:heading>
                        <flux:text>{{ __('general.sms_settings_description') }}</flux:text>
                        <flux:separator variant="subtle" />

                        <flux:field>
                            <flux:label>{{ __('general.sms_token') }}</flux:label>
                            <flux:input wire:model="smsForm.token" type="password" viewable copyable dir="ltr" />
                            <flux:error name="smsForm.token" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('general.sms_gateway') }}</flux:label>
                            <flux:input wire:model="smsForm.gateway" dir="ltr" placeholder="1000" />
                            <flux:error name="smsForm.gateway" />
                        </flux:field>

                        <flux:button type="submit" class="w-full" variant="primary" color="teal">
                            {{ __('general.save') }}
                        </flux:button>
                    </form>
                </flux:card>
            </flux:tab.panel>

            <flux:tab.panel name="maintenance">
                <flux:card>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <flux:heading size="lg">{{ __('general.maintenance_mode') }}</flux:heading>
                                <flux:text>{{ __('general.maintenance_mode_description') }}</flux:text>
                            </div>
                            <flux:badge :color="$this->isDown ? 'red' : 'green'">
                                {{ $this->isDown ? __('general.maintenance_active') : __('general.maintenance_inactive') }}
                            </flux:badge>
                        </div>
                        <flux:separator variant="subtle" />

                        @if ($this->bypassUrl)
                            <flux:callout variant="warning" icon="triangle-alert" heading="{{ __('general.maintenance_bypass_url') }}">
                                <flux:text dir="ltr" class="break-all">{{ $this->bypassUrl }}</flux:text>
                            </flux:callout>
                        @endif

                        <form wire:submit="saveMaintenance" class="space-y-4">
                            <flux:field>
                                <flux:label>{{ __('general.maintenance_message') }}</flux:label>
                                <flux:textarea wire:model="maintenanceForm.message" rows="3" />
                                <flux:error name="maintenanceForm.message" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('general.maintenance_secret') }}</flux:label>
                                <div class="flex items-start gap-2">
                                    <div class="flex-1">
                                        <flux:input
                                            wire:model="maintenanceForm.secret"
                                            dir="ltr"
                                            type="password"
                                            viewable
                                            copyable
                                        />
                                    </div>
                                    <flux:tooltip content="{{ __('general.maintenance_generate_secret') }}">
                                        <flux:button
                                            type="button"
                                            size="sm"
                                            variant="primary"
                                            color="zinc"
                                            icon="key"
                                            icon:variant="outline"
                                            wire:click="generateSecret"
                                        />
                                    </flux:tooltip>
                                </div>
                                <flux:text class="mt-1 text-xs">{{ __('general.maintenance_secret_help') }}</flux:text>
                                <flux:error name="maintenanceForm.secret" />
                            </flux:field>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <flux:field>
                                    <flux:label>{{ __('general.maintenance_retry') }}</flux:label>
                                    <flux:input wire:model="maintenanceForm.retry" type="number" min="0" dir="ltr" />
                                    <flux:error name="maintenanceForm.retry" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>{{ __('general.maintenance_refresh') }}</flux:label>
                                    <flux:input wire:model="maintenanceForm.refresh" type="number" min="0" dir="ltr" />
                                    <flux:error name="maintenanceForm.refresh" />
                                </flux:field>
                            </div>

                            <flux:button type="submit" class="w-full" variant="primary" color="teal">
                                {{ __('general.save') }}
                            </flux:button>
                        </form>

                        <flux:separator variant="subtle" />

                        @if ($this->isDown)
                            <flux:button
                                wire:click="disableMaintenance"
                                wire:confirm="{{ __('general.are_you_sure') }}"
                                variant="primary"
                                color="green"
                                icon="play"
                                class="w-full"
                            >
                                {{ __('general.maintenance_disable') }}
                            </flux:button>
                        @else
                            <flux:button
                                wire:click="enableMaintenance"
                                wire:confirm="{{ __('general.are_you_sure') }}"
                                variant="primary"
                                color="red"
                                icon="power"
                                class="w-full"
                            >
                                {{ __('general.maintenance_enable') }}
                            </flux:button>
                        @endif
                    </div>
                </flux:card>
            </flux:tab.panel>
        </flux:tab.group>
    </div>
</div>
