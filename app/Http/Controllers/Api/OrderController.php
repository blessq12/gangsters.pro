<?php

namespace App\Http\Controllers\Api;

use App\Application\Order\Contracts\MarkOrderPaidContract;
use App\Application\Order\Command\CreateOrderUseCase;
use App\Application\Order\DTO\CreateOrderDTO;
use App\Application\Order\Query\GetClientOrdersUseCase;
use App\Domain\Shopping\Entities\ShoppingSession;
use App\Http\Middleware\EnsureShoppingSession;
use App\Http\Requests\Order\StoreOrderRequest;
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

        $rows = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];
        $dto = new CreateOrderDTO(
            items: array_map(
                fn (array $row) => [
                    'product_id' => (int) $row['product_id'],
                    'quantity' => (int) $row['quantity'],
                ],
                $rows,
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
        $authenticatedClientId = is_object($user) && isset($user->id)
            ? (int) $user->id
            : null;

        $shoppingSession = $request->attributes->get(EnsureShoppingSession::ATTRIBUTE_KEY);
        $order = $useCase->execute(
            $dto,
            $authenticatedClientId,
            $shoppingSession instanceof ShoppingSession ? $shoppingSession : null,
        );

        return response()->json($order, 201);
    }

    public function markPaid(string $id, MarkOrderPaidContract $useCase): JsonResponse
    {
        $result = $useCase->execute($id);

        return response()->json($result);
    }

}
