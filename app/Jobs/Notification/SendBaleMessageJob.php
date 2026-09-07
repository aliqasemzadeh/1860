<?php

namespace App\Jobs\Notification;

use App\Settings\BaleSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendBaleMessageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $chatId,
        public string $text,
    ) {
    }

    public function handle(BaleSettings $settings): bool
    {
        $token = trim($settings->bot_token);

        if ($token === '') {
            Log::error('Bale message skipped: bot token is empty.');

            return false;
        }

        if (trim($this->chatId) === '' || trim($this->text) === '') {
            Log::error('Bale message skipped: chat id or text is empty.');

            return false;
        }

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->post('https://tapi.bale.ai/bot'.$token.'/sendMessage', [
                    'chat_id' => $this->chatId,
                    'text' => $this->text,
                ]);

            $payload = $response->json();

            if (! $response->successful() || ! ($payload['ok'] ?? false)) {
                Log::error('Failed to send Bale message.', [
                    'status' => $response->status(),
                    'chat_id' => $this->chatId,
                    'body' => $payload ?? $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send Bale message: '.$e->getMessage(), [
                'chat_id' => $this->chatId,
            ]);

            return false;
        }
    }
}
