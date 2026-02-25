<div class="max-w-4xl mx-auto py-12">
    <flux:card>
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="xl">{{ __('app.contact_us') }}</flux:heading>
                <flux:text>{{ __('app.contact_description') }}</flux:text>
            </div>

            <flux:separator variant="subtle" />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                        <flux:icon name="phone" class="size-6 text-zinc-500" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ __('app.phone') }}</flux:heading>
                        <flux:text class="mt-1">07132317274</flux:text>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                        <flux:icon name="device-phone-mobile" class="size-6 text-zinc-500" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ __('app.mobile') }}</flux:heading>
                        <flux:text class="mt-1">09177886099</flux:text>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                        <flux:icon name="envelope" class="size-6 text-zinc-500" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ __('app.email') }}</flux:heading>
                        <flux:text class="mt-1">aliqasemzadeh7@gmail.com</flux:text>
                    </div>
                </div>
            </div>
        </div>
    </flux:card>
</div>
