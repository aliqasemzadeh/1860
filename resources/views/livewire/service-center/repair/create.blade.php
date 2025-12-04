<flux:modal name="service-center.repair.admission.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.admission') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.admission_description') }}</flux:text>
        </div>
        <!-- Modal body -->
        <form wire:submit="admission" method="post">
            <div class="pb-2">
                <flux:field>
                    <flux:label>{{ __('app.mobile') }}</flux:label>

                    <flux:input wire:model="mobile" type="text" />

                    <flux:error name="mobile" />
                </flux:field>
            </div>
            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('app.admission') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
