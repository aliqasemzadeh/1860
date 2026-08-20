<x-slot name="title">
    {{ $step === 1 ? __('general.login_with_mobile') : __('general.verify_login') }}
</x-slot>
<div>
    @if ($step === 1)
        <form wire:submit="send" class="space-y-6 max-w-sm mx-auto w-full">
            <div class="space-y-2">
                <flux:heading size="lg" class="text-center">{{ __('general.login_with_mobile') }}</flux:heading>
                <flux:text class="text-center">{{ __('general.enter_mobile_prompt') }}</flux:text>
            </div>
            <flux:field>
                <flux:input dir="ltr" wire:model="mobile" />
                <flux:error name="mobile" />
            </flux:field>
            <div>
                <flux:button variant="primary" type="submit" class="w-full">{{ __('general.send_code') }}</flux:button>
            </div>
        </form>
    @else
        <form wire:submit="verify" class="space-y-8">
            <div class="max-w-64 mx-auto space-y-2">
                <flux:heading size="lg" class="text-center">{{ __('general.verify_login') }}</flux:heading>
                <flux:text class="text-center">{{ $mobile }}</flux:text>
                <flux:text class="text-center">{{ __('general.mobile_confirm_question') }}</flux:text>
                <flux:button type="button" wire:click="editPhone" variant="ghost" class="w-full">
                    {{ __('general.edit_phone_number') }}
                </flux:button>
            </div>
            <flux:otp wire:model="code" dir="ltr" length="6" label="{{ __('general.otp_code_label') }}" label:sr-only :error:icon="false" error:class="text-center" class="mx-auto" />
            <div class="space-y-4">
                <flux:button variant="primary" type="submit" class="w-full">{{ __('general.verify') }}</flux:button>
                <flux:button type="button" wire:click="resend" class="w-full">{{ __('general.resend_code') }}</flux:button>
            </div>
        </form>
    @endif
</div>
