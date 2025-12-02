<div class="max-w-md mx-auto px-4 py-8">
    <div class="flex items-start gap-4">
        <div class="flex-1">
            <flux:heading size="lg">{{ __('1860.logout.title') }}</flux:heading>
            <flux:text class="mt-2 leading-relaxed">
                {{ __('1860.logout.message_line_1') }}<br>
                {{ __('1860.logout.message_line_2') }}
            </flux:text>
        </div>
    </div>

    <div class="mt-8 flex items-center gap-4">
        <flux:button variant="primary" wire:click="cancel">
            {{ __('1860.logout.cancel') }}
        </flux:button>

        <flux:button variant="danger" wire:click="confirmLogout">
            {{ __('1860.logout.confirm') }}
        </flux:button>
    </div>
</div>
