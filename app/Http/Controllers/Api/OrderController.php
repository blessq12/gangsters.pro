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
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'delivery_method' => ['required', 'string', Rule::enum(DeliveryMethod::class)],
            'delivery_address' => ['nullable', 'array', 'required_if:delivery_method,courier'],
            'delivery_comment' => ['nullable', 'string'],
            'payment_method' => ['required', 'string', Rule::enum(PaymentMethod::class)],
        ]);

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
        );

        $order = $useCase->execute($dto);

        return response()->json($order, 201);
    }
}
