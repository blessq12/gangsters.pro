<?php

namespace App\Http\Controllers\Api;

use App\Application\Common\Exceptions\UnauthorizedException;
use App\Application\Order\DTO\GetOrderDto;
use App\Application\Order\DTO\ListClientOrdersDto;
use App\Application\Order\useCases\GetOrderUseCase;
use App\Application\Order\useCases\ListClientOrdersUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrderController extends Controller
{
    public function __construct(
        private readonly ListClientOrdersUseCase $listClientOrders,
        private readonly GetOrderUseCase $getOrder,
    ) {}

    public function show(Request $request, int $orderId): JsonResponse
    {
        return response()->json(
            $this->getOrder->execute(
                new GetOrderDto(
                    orderId: $orderId,
                    clientId: $this->resolveClientId($request),
                ),
            ),
        );
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->listClientOrders->execute(
                new ListClientOrdersDto(
                    clientId: $this->resolveClientId($request),
                ),
            ),
        ]);
    }

    private function resolveAuthenticatedClient(Request $request): Authenticatable
    {
        $client = $request->user('sanctum');

        if (! $client instanceof Authenticatable) {
            throw new UnauthorizedException();
        }

        return $client;
    }

    private function resolveClientId(Request $request): int
    {
        return (int) $this->resolveAuthenticatedClient($request)->getAuthIdentifier();
    }
}
