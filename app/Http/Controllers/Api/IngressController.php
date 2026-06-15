<?php

namespace App\Http\Controllers\Api;

use App\Application\AggregatorIngress\DTO\ReceiveExternalOrderDto;
use App\Application\AggregatorIngress\useCases\ReceiveExternalOrderUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class IngressController extends Controller
{
    public function __construct(
        private readonly ReceiveExternalOrderUseCase $receiveExternalOrder,
    ) {}

    public function store(Request $request, string $partner): JsonResponse
    {
        $payload = $request->all();
        if (! is_array($payload)) {
            $payload = [];
        }

        return response()->json(
            $this->receiveExternalOrder->execute(
                new ReceiveExternalOrderDto(
                    partnerCode: $partner,
                    apiKey: $request->header('X-Ingress-Api-Key'),
                    payload: $payload,
                ),
            ),
            200,
        );
    }
}
