<?php

namespace App\Jobs\Otp;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Config;
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
    public function handle(): void
    {
        $message = __('otp.sms_text', ['code' => $this->otp]);

        [$originalMobile, $normalizedMobile] = $this->normalizeIranMobile($this->mobile);

        $this->sendViaSabapnovin($normalizedMobile, $message, $originalMobile);
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

    private function sendViaSabapnovin(string $normalizedTo, string $text, string $originalTo): void
    {
        try {
            $request = Http::withoutVerifying()
                ->get(
                    sprintf('https://api.sabanovin.com/v1/%s/sms/send.json', (string) Config::get('sms.api-key')),
                    [
                        'gateway' => Config::get('sms.gateway'),
                        'to' => $normalizedTo,
                        'text' => $text.PHP_EOL.'لغو 11',
                    ]
                )->json();

            Log::info('SMS send attempt via Sabapnovin', [
                'to' => $normalizedTo,
                'original' => $originalTo,
                'message' => $text,
                'response' => $request,
            ]);

            if (($request['status']['code'] ?? 0) != 200) {
                Log::error('Send SMS Error: '.($request['status']['message'] ?? 'unknown error'));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send SMS: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
