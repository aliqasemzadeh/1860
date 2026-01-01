<?php

namespace App\Livewire\Main\Order;

use App\Jobs\Otp\SendOtpJob;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Tzsk\Otp\Facades\Otp;
use Flux\Flux;

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

        // Generate OTP
        $digits = (int) Config::get('otp.digits', 6);
        $expiry = (int) Config::get('otp.expiry', 2);

        $otp = Otp::digits($digits)->expiry($expiry)->generate($this->mobile);
        dispatch(new SendOtpJob($this->mobile, $otp));

        $this->code = '';
        $this->step = 2;
        $this->canResendAt = now()->addMinutes($expiry);
        Flux::toast(variant: 'success', text: __('app.code_sent'));
    }

    public function verify(): void
    {
        $this->validate([
            'mobile' => ['required', 'string'],
            'code' => ['required', 'string', 'size:'.(string) Config::get('otp.digits', 6)],
        ]);

        $valid = Otp::digits((int) Config::get('otp.digits', 6))
            ->expiry((int) Config::get('otp.expiry', 2))
            ->check($this->code, $this->mobile);

        if (! $valid) {
            Flux::toast(variant: 'danger', text: __('app.invalid_code'));
            return;
        }

        $user = User::firstOrCreate([
            'mobile' => $this->mobile,
        ]);

        Auth::login($user, true);
        request()->session()->regenerate();

        Flux::toast(variant: 'success', text: __('app.login_successful'));
        $this->redirect(route('order.shipping'), navigate: true);
    }

    public function resend(): void
    {
        $expiry = (int) Config::get('otp.expiry', 2);

        if ($this->canResendAt && now()->lt($this->canResendAt)) {
            Flux::toast(variant: 'danger', text: __('app.wait_before_resend'));
            return;
        }

        if (! $this->mobile) {
            $this->step = 1;
            Flux::toast(variant: 'danger', text: __('app.enter_mobile'));
            return;
        }

        $otp = Otp::digits((int) Config::get('otp.digits', 6))
            ->expiry($expiry)
            ->generate($this->mobile);

        dispatch(new SendOtpJob($this->mobile, $otp));
        Flux::toast(variant: 'success', text: __('app.code_resent'));

        $this->canResendAt = now()->addMinutes($expiry);
    }

    public function render()
    {
        return view('livewire.main.order.checkout');
    }
}
