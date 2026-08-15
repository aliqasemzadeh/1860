<div>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('general.profile') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('general.user_dashboard_description') }}</flux:subheading>
            </div>
            @can('user_profile_update')
                <flux:tooltip content="{{ __('general.edit_profile') }}">
                    <flux:button
                        size="sm"
                        variant="primary"
                        color="teal"
                        icon="pencil"
                        icon:variant="outline"
                        wire:click="$dispatch('panel.user.dashboard.edit.assign-data')"
                    />
                </flux:tooltip>
            @endcan
        </div>

        <flux:separator variant="subtle" />
    </div>

    <livewire:panel.user.dashboard.edit />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <flux:card class="lg:col-span-2 space-y-4">
            <flux:heading size="lg">{{ __('general.account_information') }}</flux:heading>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-zinc-500">{{ __('general.first_name') }}</flux:text>
                    <flux:text class="font-medium">{{ $this->user->first_name ?: '-' }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500">{{ __('general.last_name') }}</flux:text>
                    <flux:text class="font-medium">{{ $this->user->last_name ?: '-' }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500">{{ __('general.mobile') }}</flux:text>
                    <flux:text class="font-medium">{{ $this->user->mobile }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500">{{ __('general.email') }}</flux:text>
                    <flux:text class="font-medium">{{ $this->user->email ?: '-' }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500">{{ __('general.national_code') }}</flux:text>
                    <flux:text class="font-medium" dir="ltr">{{ $this->user->national_code ?: '-' }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500">{{ __('general.member_since') }}</flux:text>
                    <flux:text class="font-medium">
                        {{ jalali($this->user->created_at, 'Y/m/d') }}
                    </flux:text>
                </div>
            </div>
        </flux:card>

        <div class="space-y-4">
            <a href="{{ route('panel.user.order.index') }}" wire:navigate class="block">
                <flux:card class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                    <flux:text class="text-sm text-zinc-500">{{ __('general.orders_count') }}</flux:text>
                    <flux:heading size="xl">{{ $this->stats['orders_count'] }}</flux:heading>
                </flux:card>
            </a>
            <a href="{{ route('panel.user.order.index') }}" wire:navigate class="block">
                <flux:card class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                    <flux:text class="text-sm text-zinc-500">{{ __('general.unpaid_orders_count') }}</flux:text>
                    <flux:heading size="xl" class="text-orange-600">{{ $this->stats['unpaid_count'] }}</flux:heading>
                </flux:card>
            </a>
            <a href="{{ route('panel.user.address.index') }}" wire:navigate class="block">
                <flux:card class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                    <flux:text class="text-sm text-zinc-500">{{ __('general.addresses_count') }}</flux:text>
                    <flux:heading size="xl">{{ $this->stats['addresses_count'] }}</flux:heading>
                </flux:card>
            </a>
        </div>
    </div>
</div>
