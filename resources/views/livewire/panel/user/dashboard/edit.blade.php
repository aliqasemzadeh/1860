<div>
    <flux:modal name="panel.user.dashboard.edit.modal" class="md:w-96" flyout position="right">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('app.edit_profile') }}</flux:heading>
                <flux:text class="mt-2">{{ __('app.edit_profile_description') }}</flux:text>
            </div>

            <form wire:submit="save" method="post">
                <div class="pb-2 space-y-3">
                    <flux:field>
                        <flux:label>{{ __('app.first_name') }}</flux:label>
                        <flux:input wire:model="form.first_name" type="text" />
                        <flux:error name="form.first_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.last_name') }}</flux:label>
                        <flux:input wire:model="form.last_name" type="text" />
                        <flux:error name="form.last_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.email') }}</flux:label>
                        <flux:input wire:model="form.email" type="email" />
                        <flux:error name="form.email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('app.mobile') }}</flux:label>
                        <flux:input type="text" value="{{ auth()->user()->mobile }}" disabled />
                        <flux:description>{{ __('app.mobile_cannot_be_changed') }}</flux:description>
                    </flux:field>
                </div>

                <flux:button type="submit" class="w-full" variant="primary" color="green">
                    {{ __('app.save') }}
                </flux:button>
            </form>
        </div>
    </flux:modal>
</div>
