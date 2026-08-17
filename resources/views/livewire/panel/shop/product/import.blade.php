<flux:modal name="panel.shop.product.import.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.import_excel') }}</flux:heading>
            <flux:text class="mt-2">{{ __('general.import_products_description') }}</flux:text>
        </div>

        <flux:button
            type="button"
            variant="ghost"
            icon="arrow-down-tray"
            class="w-full"
            wire:click="downloadTemplate"
        >
            {{ __('general.download_import_template') }}
        </flux:button>

        <form wire:submit="import" method="post">
            <div class="space-y-6">
                <flux:field>
                    <flux:label>{{ __('general.file') }}</flux:label>
                    <flux:input type="file" wire:model="file" accept=".xlsx,.xls,.csv" />
                    <flux:error name="file" />
                </flux:field>
            </div>

            <flux:button type="submit" class="w-full mt-6" variant="primary" color="teal">
                {{ __('general.import_products') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
