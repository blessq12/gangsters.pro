<?php

namespace App\Infrastructure\Integration\Frontpad;

use Illuminate\Support\Facades\Http;

final class FrontpadApiClient
{
    /**
     * @param  array<string, mixed>  $formParams
     * @return array<string, mixed>
     */
    public function createOrder(array $formParams): array
    {
        $endpoint = $this->resolveEndpoint();

        FrontpadLogger::info('Frontpad API request', [
            'direction' => 'outbound',
            'operation' => 'new_order',
            'endpoint' => $endpoint,
            'payload' => FrontpadLogger::sanitizePayload($formParams),
        ]);

        $response = Http::timeout(15)
            ->asForm()
            ->post($endpoint, $formParams);

        if (! $response->successful()) {
            FrontpadLogger::error('Frontpad API HTTP error', [
                'direction' => 'outbound',
                'operation' => 'new_order',
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
            FrontpadLogger::error('Frontpad API invalid response', [
                'direction' => 'outbound',
                'operation' => 'new_order',
                'body' => $response->body(),
            ]);

            return [
                'result' => 'error',
                'error' => 'invalid_response',
                'message' => $response->body(),
            ];
        }

        FrontpadLogger::info('Frontpad API response', [
            'direction' => 'outbound',
            'operation' => 'new_order',
            'response' => $decoded,
        ]);

        return $decoded;
    }

    private function resolveEndpoint(): string
    {
        $endpoint = (string) config(
            'frontpad.endpoint',
            'https://app.frontpad.ru/api/index.php?new_order',
        );

        if (str_contains($endpoint, '?')) {
            return $endpoint;
        }

        return rtrim($endpoint, '/').'?new_order';
    }
}
