<?php

namespace App\Infrastructure\OrderAccountingExport\Client;

use Illuminate\Support\Facades\Http;

final class FrontpadApiClient
{
    /**
     * @param  array<string, mixed>  $formParams
     * @return array<string, mixed>
     */
    public function createOrder(array $formParams): array
    {
        $endpoint = (string) config(
            'order-accounting-export.systems.frontpad.endpoint',
            'https://app.frontpad.ru/api/index.php?new_order',
        );

        $response = Http::timeout(15)
            ->asForm()
            ->post($endpoint, $formParams);

        if (! $response->successful()) {
            return [
                'result' => 'error',
                'error' => sprintf('http_%d', $response->status()),
                'message' => $response->body(),
            ];
        }

        $decoded = $response->json();
        if (! is_array($decoded)) {
            return [
                'result' => 'error',
                'error' => 'invalid_response',
                'message' => $response->body(),
            ];
        }

        return $decoded;
    }
}
