<?php

namespace App\Jobs\Otp;

use App\Settings\SmsSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendOtpJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $mobile, public string $otp)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(SmsSettings $settings): void
    {
        $message = __('otp.sms_text', ['code' => $this->otp]);

        [$originalMobile, $normalizedMobile] = $this->normalizeIranMobile($this->mobile);

        $this->sendViaStarSms($normalizedMobile, $message, $originalMobile, $settings);
    }

    /**
     * Normalize Iranian mobile to start with 98 while preserving original.
     * Returns [original, normalized].
     */
    private function normalizeIranMobile(string $mobile): array
    {
        $original = trim($mobile);

        $m = preg_replace('/[^0-9+]/', '', $original) ?? $original;

        if (str_starts_with($m, '+98')) {
            $m = substr($m, 1);
        }

        if (str_starts_with($m, '0')) {
            $m = '98'.substr($m, 1);
        }

        if (str_starts_with($m, '98')) {
            return [$original, $m];
        }

        if (str_starts_with($m, '9') && strlen($m) >= 10) {
            $m = '98'.$m;
        }

        return [$original, $m];
    }

    private function sendViaStarSms(string $normalizedTo, string $text, string $originalTo, SmsSettings $settings): void
    {
        try {
            $response = Http::withoutVerifying()
                ->withToken($settings->token)
                ->post('https://srscrm.ir/api/sms/send', [
                    'gateway' => $settings->gateway,
                    'to' => $normalizedTo,
                    'message' => $text . PHP_EOL . 'لغو11',
                ])->json();

            Log::info('SMS send attempt via Star SMS', [
                'to' => $normalizedTo,
                'original' => $originalTo,
                'message' => $text,
                'response' => $response,
            ]);

            if (!($response['ok'] ?? false)) {
                Log::error('Send SMS Error: '.($response['message'] ?? 'unknown error'), [
                    'code' => $response['code'] ?? 'no_code'
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send SMS: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
