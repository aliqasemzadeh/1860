<div xmlns:flux="http://www.w3.org/1999/html">
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
                <flux:heading size="xl" level="1">{{ __('app.function') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.function_description') }}</flux:subheading>
        </div>

        <flux:separator variant="subtle" />

        <div class="space-y-4">
            <flux:button wire:click="updatePermissions">{{ __('app.update_permissions') }}</flux:button>
            <flux:button wire:click="updatePermissions">{{ __('app.update_permissions') }}</flux:button>
        </div>
    </div>
</div>
