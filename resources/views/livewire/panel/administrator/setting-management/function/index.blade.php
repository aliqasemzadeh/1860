<div>
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('app.function') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('app.function_description') }}</flux:subheading>
        <flux:separator variant="subtle" />

        <div class="mt-6 space-y-4">
            <flux:button wire:click="updatePermissions" variant="primary" color="indigo" class="w-full">
                {{ __('app.update_permissions') }}
            </flux:button>

            <flux:button wire:click="clearCache" variant="primary" color="zinc" class="w-full">
                {{ __('app.clear_cache') }}
            </flux:button>

            <flux:button wire:click="rebuildSitemap" variant="primary" color="sky" class="w-full">
                {{ __('app.rebuild_sitemap') }}
            </flux:button>

            <flux:button wire:click="addWatermarks" variant="primary" color="cyan" class="w-full">
                {{ __('app.add_watermarks') }}
            </flux:button>

            <flux:separator variant="subtle" />

            <flux:button
                wire:click="updateQuick"
                wire:confirm="{{ __('app.are_you_sure') }}"
                variant="primary"
                color="teal"
                class="w-full"
            >
                {{ __('app.quick_update') }}
            </flux:button>

            <flux:button
                wire:click="updateFull"
                wire:confirm="{{ __('app.are_you_sure') }}"
                variant="primary"
                color="orange"
                class="w-full"
            >
                {{ __('app.full_update') }}
            </flux:button>
        </div>
    </div>
</div>
