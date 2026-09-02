<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ValidateTorobToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Torob-Token');
        $version = $request->header('X-Torob-Token-Version');

        if (! is_string($token) || $token === '' || $version !== '1') {
            return $this->unauthorized();
        }

        try {
            $publicKey = (string) config('services.torob.public_key');
            $decodedKey = base64_decode($publicKey, true);
            if ($decodedKey === false || strlen($decodedKey) < 32) {
                throw new \RuntimeException('Torob public key is not configured correctly.');
            }

            $seed = base64_encode(substr($decodedKey, -32));
            $payload = JWT::decode($token, new Key($seed, 'EdDSA'));
            $expectedAudience = (string) config('services.torob.audience');
            $audiences = is_array($payload->aud ?? null)
                ? $payload->aud
                : [(string) ($payload->aud ?? '')];

            if ($expectedAudience === '' || ! in_array($expectedAudience, $audiences, true)) {
                throw new \RuntimeException('Torob token audience does not match this shop.');
            }
        } catch (Throwable) {
            return $this->unauthorized();
        }

        return $next($request);
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json(['error' => 'Unauthorized Torob request.'], 401);
    }
}
