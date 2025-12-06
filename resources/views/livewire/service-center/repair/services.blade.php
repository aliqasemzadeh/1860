<flux:modal name="service-center.repair.services.modal" class="md:w-2/3" flyout position="right">
    @isset($this->repair)
        <div class="flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="lg" level="2">
                        {{ __('app.repair_services') }}
                    </flux:heading>
                    <flux:subheading size="md">
                        {{ __('app.repair_services_description') }}
                    </flux:subheading>
                </div>
            </div>
        </div>


    @endisset
</flux:modal>
