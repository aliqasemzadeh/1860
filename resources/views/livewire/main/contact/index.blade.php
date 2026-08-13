<div class="max-w-4xl mx-auto py-12">
    <flux:card>
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="xl">{{ __('general.contact_us') }}</flux:heading>
                <flux:text>{{ __('app.contact_description') }}</flux:text>
            </div>

            <flux:separator variant="subtle" />

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                @if (filled($this->contact->phone))
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                            <flux:icon name="phone" class="size-6 text-zinc-500" />
                        </div>
                        <div>
                            <flux:heading size="lg">{{ __('app.landline') }}</flux:heading>
                            <flux:text class="mt-1" dir="ltr">{{ $this->contact->phone }}</flux:text>
                        </div>
                    </div>
                @endif

                @if (filled($this->contact->mobile))
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                            <flux:icon name="device-phone-mobile" class="size-6 text-zinc-500" />
                        </div>
                        <div>
                            <flux:heading size="lg">{{ __('general.mobile') }}</flux:heading>
                            <flux:text class="mt-1" dir="ltr">{{ $this->contact->mobile }}</flux:text>
                        </div>
                    </div>
                @endif

                @if (filled($this->contact->email))
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                            <flux:icon name="envelope" class="size-6 text-zinc-500" />
                        </div>
                        <div>
                            <flux:heading size="lg">{{ __('app.email') }}</flux:heading>
                            <flux:text class="mt-1" dir="ltr">{{ $this->contact->email }}</flux:text>
                        </div>
                    </div>
                @endif

                @if (filled($this->contact->address))
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                            <flux:icon name="map-pin" class="size-6 text-zinc-500" />
                        </div>
                        <div>
                            <flux:heading size="lg">{{ __('app.address') }}</flux:heading>
                            <flux:text class="mt-1">{{ $this->contact->address }}</flux:text>
                        </div>
                    </div>
                @endif
            </div>

            @if (count($this->socials) > 0)
                <flux:separator variant="subtle" />
                <div class="flex flex-wrap gap-3">
                    @foreach ($this->socials as $key => $url)
                        @php($network = \App\Enums\SocialNetworkEnum::from($key))
                        <flux:tooltip content="{{ $network->label() }}">
                            <flux:button
                                :href="$url"
                                target="_blank"
                                rel="noopener noreferrer"
                                size="sm"
                                variant="primary"
                                color="sky"
                                :icon="$network->icon()"
                                icon:variant="outline"
                            />
                        </flux:tooltip>
                    @endforeach
                </div>
            @endif
        </div>
    </flux:card>
</div>
