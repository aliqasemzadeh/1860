<form wire:submit="verify" class="space-y-8">
    <div class="max-w-64 mx-auto space-y-2">
        <flux:heading size="lg" class="text-center">تایید ورود</flux:heading>
        <flux:text class="text-center">{{ $mobile }}</flux:text>
    </div>
    <flux:otp wire:model="code" length="6" label="OTP Code" label:sr-only :error:icon="false" error:class="text-center" class="mx-auto" />
    <div class="space-y-4">
        <flux:button variant="primary" type="submit" class="w-full">Verify</flux:button>
        <flux:button wire:click="resend" class="w-full">Resend code</flux:button>
    </div>
</form>

