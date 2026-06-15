<?php

namespace App\Infrastructure\OrderAccountingExport\Client;

use Illuminate\Support\Facades\Http;

final class IikoApiClient
{
    private ?string $accessToken = null;

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function createDelivery(array $body): array
    {
        $response = Http::timeout(20)
            ->withToken($this->resolveAccessToken())
            ->acceptJson()
            ->post($this->endpoint('/api/1/deliveries/create'), $body);

        return $this->decodeResponse($response->status(), $response->json(), $response->body());
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @return array<string, mixed>
     */
    public function commandStatus(?array $body): array
    {
        $response = Http::timeout(15)
            ->withToken($this->resolveAccessToken())
            ->acceptJson()
            ->post($this->endpoint('/api/1/commands/status'), $body ?? []);

        return $this->decodeResponse($response->status(), $response->json(), $response->body());
    }

    private function resolveAccessToken(): string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        $apiLogin = (string) config('order-accounting-export.systems.iiko.api_login', '');
        if ($apiLogin === '') {
            throw new \InvalidArgumentException('Не задан OAE_IIKO_API_LOGIN.');
        }

        $response = Http::timeout(15)
            ->acceptJson()
            ->post($this->endpoint('/api/1/access_token'), [
                'apiLogin' => $apiLogin,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException(sprintf('iiko access_token: HTTP %d.', $response->status()));
        }

        $token = $response->json('token');
        if (! is_string($token) || $token === '') {
            throw new \RuntimeException('iiko access_token: пустой token в ответе.');
        }

        $this->accessToken = $token;

        return $this->accessToken;
    }

    private function endpoint(string $path): string
    {
        $baseUrl = rtrim((string) config(
            'order-accounting-export.systems.iiko.base_url',
            'https://api-ru.iiko.services',
        ), '/');

        return $baseUrl.$path;
    }

    /**
     * @param  array<string, mixed>|null  $decoded
     * @return array<string, mixed>
     */
    private function decodeResponse(int $status, ?array $decoded, string $rawBody): array
    {
        if (! is_array($decoded)) {
            return [
                'error' => 'invalid_response',
                'httpStatus' => $status,
                'message' => $rawBody,
            ];
        }

        $decoded['httpStatus'] = $status;

        return $decoded;
    }
}
