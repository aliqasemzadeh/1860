<?php

namespace App\Livewire\Main\Order;

use App\Jobs\Otp\SendOtpJob;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Checkout extends Component
{
    public string $mobile = '';

    public string $code = '';

    public int $step = 1;

    public ?\Carbon\Carbon $canResendAt = null;

    public function mount()
    {
        // If user is already authenticated, redirect to shipping
        if (auth()->check()) {
            return $this->redirect(route('order.shipping'), navigate: true);
        }
    }

    public function send(): void
    {
        $this->validate([
            'mobile' => ['required', 'string', 'ir_mobile'],
        ]);

        $user = User::firstOrCreate([
            'mobile' => $this->mobile,
        ]);

        $otp = $user->createOneTimePassword();

        dispatch(new SendOtpJob($this->mobile, $otp->password));

        $this->code = '';
        $this->step = 2;
        $this->canResendAt = now()->addMinutes((int) config('one-time-passwords.default_expires_in_minutes'));
        Flux::toast(variant: 'success', text: __('general.code_sent'));
    }

    public function verify(): void
    {
        $this->validate([
            'mobile' => ['required', 'string'],
            'code' => ['required', 'string', 'size:'.(string) config('one-time-passwords.password_length', 6)],
        ]);

        $user = User::where('mobile', $this->mobile)->first();

        if (! $user) {
            Flux::toast(variant: 'danger', text: __('general.invalid_code'));

            return;
        }

        $result = $user->attemptLoginUsingOneTimePassword($this->code, true);

        if (! $result->isOk()) {
            Flux::toast(variant: 'danger', text: __('general.invalid_code'));

            return;
        }

        request()->session()->regenerate();

        Flux::toast(variant: 'success', text: __('general.login_successful'));
        $this->redirect(route('order.shipping'), navigate: true);
    }

    public function resend(): void
    {
        $expiry = (int) config('one-time-passwords.default_expires_in_minutes');

        if ($this->canResendAt && now()->lt($this->canResendAt)) {
            Flux::toast(variant: 'danger', text: __('general.wait_before_resend'));

            return;
        }

        if (! $this->mobile) {
            $this->step = 1;
            Flux::toast(variant: 'danger', text: __('general.enter_mobile'));

            return;
        }

        $user = User::firstOrCreate([
            'mobile' => $this->mobile,
        ]);

        $otp = $user->createOneTimePassword();

        dispatch(new SendOtpJob($this->mobile, $otp->password));
        Flux::toast(variant: 'success', text: __('general.code_resent'));

        $this->canResendAt = now()->addMinutes($expiry);
    }

    public function render()
    {
        return view('livewire.main.order.checkout');
    }
}
