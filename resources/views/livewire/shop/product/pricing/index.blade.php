<div>
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item href="#">Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="#">Blog</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Post</flux:breadcrumbs.item>
    </flux:breadcrumbs>
    <div class="relative mb-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('app.pricing') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('app.pricing_description') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="shop.product.pricing.create.modal">
                <flux:button variant="primary">{{ __('app.create_product') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:separator variant="subtle" />
    </div>
    <livewire:shop.product.pricing.create />
</div>
