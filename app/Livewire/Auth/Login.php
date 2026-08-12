<?php

namespace App\Livewire\Auth;

use App\Jobs\Otp\SendOtpJob;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

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

        $user = User::firstOrCreate([
            'mobile' => $this->mobile,
        ]);

        $otp = $user->createOneTimePassword();

        Log::info('OTP generated', ['otp' => $otp->password]);

        dispatch(new SendOtpJob($this->mobile, $otp->password));

        $this->code = '';
        $this->step = 2;
        $this->canResendAt = now()->addMinutes((int) config('one-time-passwords.default_expires_in_minutes'));
        Flux::toast(variant: 'success', text: 'کد ارسال شد.');
    }

    public function verify(): void
    {
        $this->validate([
            'mobile' => ['required', 'string'],
            'code' => ['required', 'string', 'size:'.(string) config('one-time-passwords.password_length', 6)],
        ]);

        $user = User::where('mobile', $this->mobile)->first();

        if (! $user) {
            Flux::toast(variant: 'danger', text: 'کد وارد شده اشتباه است.');

            return;
        }

        $result = $user->attemptLoginUsingOneTimePassword($this->code, true);

        if (! $result->isOk()) {
            Flux::toast(variant: 'danger', text: 'کد وارد شده اشتباه است.');

            return;
        }

        request()->session()->regenerate();

        $this->redirectIntended(route('home'));
    }

    public function resend(): void
    {
        $expiry = (int) config('one-time-passwords.default_expires_in_minutes');

        if ($this->canResendAt && now()->lt($this->canResendAt)) {
            Flux::toast(variant: 'danger', text: 'برای ارسال مجدد باید دو دقیقه صبر کنید.');

            return;
        }

        if (! $this->mobile) {
            $this->step = 1;
            Flux::toast(variant: 'danger', text: 'شماره همراه را وارد کنید.');

            return;
        }

        $user = User::firstOrCreate([
            'mobile' => $this->mobile,
        ]);

        $otp = $user->createOneTimePassword();

        dispatch(new SendOtpJob($this->mobile, $otp->password));

        Flux::toast(variant: 'success', text: 'کد مجدد ارسال شد.');

        $this->canResendAt = now()->addMinutes($expiry);
    }

    #[Layout('layouts.auth')]
    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
