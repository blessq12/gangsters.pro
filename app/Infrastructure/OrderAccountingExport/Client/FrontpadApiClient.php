<?php

namespace App\Infrastructure\OrderAccountingExport\Client;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class FrontpadApiClient
{
    /**
     * @param  array<string, mixed>  $formParams
     * @return array<string, mixed>
     */
    public function createOrder(array $formParams): array
    {
        $endpoint = $this->resolveEndpoint();

        Log::info('Frontpad API request', [
            'endpoint' => $endpoint,
            'payload' => $this->sanitizePayloadForLog($formParams),
        ]);

        $response = Http::timeout(15)
            ->asForm()
            ->post($endpoint, $formParams);

        if (! $response->successful()) {
            Log::error('Frontpad API HTTP error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'result' => 'error',
                'error' => sprintf('http_%d', $response->status()),
                'message' => $response->body(),
            ];
        }

        $decoded = $response->json();
        if (! is_array($decoded)) {
            Log::error('Frontpad API invalid response', [
                'body' => $response->body(),
            ]);

            return [
                'result' => 'error',
                'error' => 'invalid_response',
                'message' => $response->body(),
            ];
        }

        Log::debug('Frontpad API response', $decoded);

        return $decoded;
    }

    private function resolveEndpoint(): string
    {
        $endpoint = (string) config(
            'order-accounting-export.systems.frontpad.endpoint',
            'https://app.frontpad.ru/api/index.php?new_order',
        );

        if (str_contains($endpoint, '?')) {
            return $endpoint;
        }

        return rtrim($endpoint, '/').'?new_order';
    }

    /**
     * @param  array<string, mixed>  $formParams
     * @return array<string, mixed>
     */
    private function sanitizePayloadForLog(array $formParams): array
    {
        $payload = $formParams;

        if (array_key_exists('secret', $payload)) {
            $payload['secret'] = '***';
        }

        return $payload;
    }
}
