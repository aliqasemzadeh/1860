<?php

namespace App\Livewire\Auth;

use App\Jobs\Otp\SendOtpJob;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Tzsk\Otp\Facades\Otp;
use Flux\Flux;

class Login extends Component
{
    public string $mobile = '';

    public string $code = '';

    public int $step = 1;

    public ?\Carbon\Carbon $canResendAt = null;

    public function send(): void
    {
        $this->validate([
            'mobile' => ['required', 'string', 'ir_mobile'],
        ]);

        // Generate OTP tied to the mobile for configured digits & expiry
        $digits = (int) Config::get('otp.digits', 6);
        $expiry = (int) Config::get('otp.expiry', 2);

        $otp = Otp::digits($digits)->expiry($expiry)->generate($this->mobile);

        // Queue the job to send the OTP via preferred channel
        dispatch(new SendOtpJob($this->mobile, $otp));

        $this->code = '';
        $this->step = 2;
        $this->canResendAt = now()->addMinutes($expiry);
        Flux::toast(variant: 'success', text: 'کد ارسال شد.');
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
            Flux::toast(variant: 'danger', text: 'کد وارد شده اشتباه است.');

            return;
        }

        $user = User::firstOrCreate([
            'mobile' => $this->mobile,
        ]);

        Auth::login($user, true);
        request()->session()->regenerate();

        $this->redirectIntended(route('home'));
    }

    public function resend(): void
    {
        $expiry = (int) Config::get('otp.expiry', 2);

        if ($this->canResendAt && now()->lt($this->canResendAt)) {
            Flux::toast(variant: 'danger', text: 'برای ارسال مجدد باید دو دقیقه صبر کنید.');
            return;
        }

        if (! $this->mobile) {
            $this->step = 1;
            Flux::toast(variant: 'danger', text: 'شماره همراه را وارد کنید.');
            return;
        }

        $otp = Otp::digits((int) Config::get('otp.digits', 6))
            ->expiry($expiry)
            ->generate($this->mobile);

        dispatch(new SendOtpJob($this->mobile, $otp));

        Flux::toast(variant: 'success', text: 'کد مجدد ارسال شد.');

        $this->canResendAt = now()->addMinutes($expiry);
    }

    #[Layout('layouts.auth')]
    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
