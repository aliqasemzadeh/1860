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
        public ?string $botToken = null,
    ) {
    }

    /**
     * @return array{ok: bool, error: string|null}
     */
    public function handle(BaleSettings $settings): array
    {
        $token = trim($this->botToken ?? $settings->bot_token);
        $chatId = trim($this->chatId);
        $text = trim($this->text);

        if ($token === '') {
            $error = 'Bale bot token is empty.';
            Log::error($error, $this->logContext());

            return $this->failure($error);
        }

        if ($chatId === '') {
            $error = 'Bale chat_id is empty.';
            Log::error($error, $this->logContext());

            return $this->failure($error);
        }

        if ($text === '') {
            $error = 'Bale message text is empty.';
            Log::error($error, $this->logContext());

            return $this->failure($error);
        }

        $endpoint = 'https://tapi.bale.ai/bot'.$token.'/sendMessage';

        try {
            Log::info('Sending Bale message.', $this->logContext([
                'endpoint' => 'https://tapi.bale.ai/bot***/sendMessage',
                'text_length' => mb_strlen($text),
            ]));

            $response = Http::asJson()
                ->acceptJson()
                ->timeout(20)
                ->post($endpoint, [
                    'chat_id' => is_numeric($chatId) ? (int) $chatId : $chatId,
                    'text' => $this->text,
                ]);

            $payload = $response->json();
            $body = is_array($payload) ? $payload : ['raw' => $response->body()];

            if (! $response->successful() || ! ($payload['ok'] ?? false)) {
                $apiDescription = data_get($payload, 'description')
                    ?? data_get($payload, 'error')
                    ?? data_get($payload, 'message')
                    ?? $response->body();

                $error = sprintf(
                    'Bale API error (HTTP %s): %s',
                    $response->status(),
                    is_string($apiDescription) ? $apiDescription : json_encode($apiDescription, JSON_UNESCAPED_UNICODE)
                );

                Log::error('Failed to send Bale message.', $this->logContext([
                    'status' => $response->status(),
                    'response' => $body,
                    'error' => $error,
                ]));

                return $this->failure($error);
            }

            Log::info('Bale message sent successfully.', $this->logContext([
                'message_id' => data_get($payload, 'result.message_id'),
            ]));

            return ['ok' => true, 'error' => null];
        } catch (\Throwable $e) {
            $error = 'Bale request exception: '.$e->getMessage();

            Log::error($error, $this->logContext([
                'exception' => $e::class,
                'trace' => $e->getTraceAsString(),
            ]));

            return $this->failure($error);
        }
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function logContext(array $extra = []): array
    {
        $token = trim($this->botToken ?? '');

        return array_merge([
            'chat_id' => $this->chatId,
            'token_prefix' => $token !== '' ? substr($token, 0, 8).'***' : null,
            'has_token_override' => $this->botToken !== null,
        ], $extra);
    }

    /**
     * @return array{ok: bool, error: string}
     */
    private function failure(string $error): array
    {
        return ['ok' => false, 'error' => $error];
    }
}
