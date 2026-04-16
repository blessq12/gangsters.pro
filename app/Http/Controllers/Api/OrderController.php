<?php

namespace App\Http\Controllers\Api;

use App\Application\Order\Command\CreateOrderUseCase;
use App\Application\Order\DTO\CreateOrderDTO;
use App\Application\Order\Query\GetClientOrdersUseCase;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Infrastructure\Client\Model\UR_Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class OrderController extends Controller
{
    public function __construct()
    {
    }

    public function index(GetClientOrdersUseCase $useCase): JsonResponse
    {
        $result = $useCase->execute();

        return response()->json($result);
    }

    public function store(StoreOrderRequest $request, CreateOrderUseCase $useCase): JsonResponse
    {
        $payload = $request->validated();

        $guestName = $payload['customer_name'] ?? null;
        $guestPhone = $payload['customer_phone'] ?? null;
        $guestEmail = $payload['customer_email'] ?? null;

        $dto = new CreateOrderDTO(
            items: array_map(
                fn (array $row) => [
                    'product_id' => (int) $row['product_id'],
                    'quantity' => (int) $row['quantity'],
                ],
                $payload['items'],
            ),
            deliveryMethod: $payload['delivery_method'],
            deliveryAddress: $payload['delivery_address'] ?? null,
            deliveryComment: $payload['delivery_comment'] ?? null,
            paymentMethod: $payload['payment_method'],
            guestCustomerName: is_string($guestName) ? $guestName : null,
            guestCustomerPhone: is_string($guestPhone) ? $guestPhone : null,
            guestCustomerEmail: is_string($guestEmail) ? $guestEmail : null,
        );

        $user = $request->user('sanctum');
        $authenticatedClientId = $user instanceof UR_Client ? (int) $user->id : null;

        $order = $useCase->execute($dto, $authenticatedClientId);

        return response()->json($order, 201);
    }

}
