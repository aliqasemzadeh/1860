<div>
    @if ($step === 1)
        <form wire:submit="send" class="space-y-6 max-w-sm mx-auto w-full">
            <div class="space-y-2">
                <flux:heading size="lg" class="text-center">{{ __('app.login_with_mobile') }}</flux:heading>
                <flux:text class="text-center">{{ __('app.enter_mobile_prompt') }}</flux:text>
            </div>
            <flux:field>
                <flux:input dir="ltr" wire:model="mobile" />
                <flux:error name="mobile" />
            </flux:field>
            <div>
                <flux:button variant="primary" type="submit" class="w-full">{{ __('app.send_code') }}</flux:button>
            </div>
        </form>
    @else
        <form wire:submit="verify" class="space-y-8">
            <div class="max-w-64 mx-auto space-y-2">
                <flux:heading size="lg" class="text-center">{{ __('app.verify_login') }}</flux:heading>
                <flux:text class="text-center">{{ $mobile }}</flux:text>
                <flux:text class="text-center">{{ __('app.mobile_confirm_question') }}</flux:text>
            </div>
            <flux:otp wire:model="code" dir="ltr" length="6" label="{{ __('app.otp_code_label') }}" label:sr-only :error:icon="false" error:class="text-center" class="mx-auto" />
            <div class="space-y-4">
                <flux:button variant="primary" type="submit" class="w-full">{{ __('app.verify') }}</flux:button>
                <flux:button type="button" wire:click="resend" class="w-full">{{ __('app.resend_code') }}</flux:button>
            </div>
        </form>
    @endif
</div>
