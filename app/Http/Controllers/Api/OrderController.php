<?php

namespace App\Http\Controllers\Api;

use App\Application\Order\Command\CreateOrderUseCase;
use App\Application\Order\DTO\CreateOrderDTO;
use App\Application\Order\Query\GetClientOrdersUseCase;
use App\Domain\Order\Enums\DeliveryMethod;
use App\Domain\Order\Enums\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use LogicException;

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

    public function store(Request $request, CreateOrderUseCase $useCase): JsonResponse
    {
        $payload = $request->validate([
            'client_id' => ['nullable', 'integer'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'delivery_method' => ['required', 'string', Rule::enum(DeliveryMethod::class)],
            'delivery_address' => ['nullable', 'array', 'required_if:delivery_method,courier'],
            'delivery_comment' => ['nullable', 'string'],
            'payment_method' => ['required', 'string', Rule::enum(PaymentMethod::class)],
        ]);

        $dto = new CreateOrderDTO(
            clientId: $payload['client_id'] ?? null,
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
        );

        try {
            $order = $useCase->execute($dto);
        } catch (LogicException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($order, 201);
    }
}
