<?php

namespace App\Jobs\Notification;

use App\Settings\SmsSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSmsMessageJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $mobile,
        public string $text
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(SmsSettings $settings): void
    {
        // Normalize and send SMS via Star SMS (srscrm.ir)
        [$originalMobile, $normalizedMobile] = $this->normalizeIranMobile($this->mobile);

        $this->sendViaStarSms($normalizedMobile, $this->text, $originalMobile, $settings);
    }

    /**
     * Normalize Iranian mobile to start with 98 while preserving original.
     * Returns [original, normalized].
     */
    private function normalizeIranMobile(string $mobile): array
    {
        $original = trim($mobile);

        // Remove spaces, dashes and plus sign
        $m = preg_replace('/[^0-9+]/', '', $original) ?? $original;

        // Convert leading +98 to 98
        if (str_starts_with($m, '+98')) {
            $m = substr($m, 1); // remove +
        }

        // If starts with 0, replace with 98
        if (str_starts_with($m, '0')) {
            $m = '98'.substr($m, 1);
        }

        // If already starts with 98, keep
        if (str_starts_with($m, '98')) {
            return [$original, $m];
        }

        // If starts with 9 and looks like 9xxxxxxxxx, prepend 98
        if (str_starts_with($m, '9') && strlen($m) >= 10) {
            $m = '98'.$m;
        }

        return [$original, $m];
    }

    /**
     * Send SMS via Star SMS using provided gateway.
     */
    private function sendViaStarSms(string $normalizedTo, string $text, string $originalTo, SmsSettings $settings): void
    {
        try {
            $response = Http::withToken($settings->token)
                ->post('https://srscrm.ir/api/sms/send', [
                    'gateway' => $settings->gateway,
                    'to' => $normalizedTo,
                    'message' => $text,
                ])->json();

            // Optional: info log and simple auditing
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
