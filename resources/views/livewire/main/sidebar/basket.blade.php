<div>
    <flux:modal.trigger name="main.sidebar.basket">
        <flux:button icon="shopping-cart" />
    </flux:modal.trigger>
    <flux:modal name="main.sidebar.basket" position="right" flyout>
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">سبد خرید</flux:heading>
                <flux:text class="mt-2">Make changes to your personal details.</flux:text>
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">تکمیل سفارش</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
