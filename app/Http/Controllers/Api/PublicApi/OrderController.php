<?php

namespace App\Http\Controllers\Api\PublicApi;

use App\Application\Order\Command\CreateOrderUseCase;
use App\Application\Order\DTO\CreateOrderDto;
use App\Application\Order\DTO\QuoteOrderDto;
use App\Application\Order\Query\QuoteOrderUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class OrderController extends Controller
{
    public function quote(Request $request, QuoteOrderUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'integer', 'min:1'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'deliveryMethod' => ['required', 'string', 'in:pickup,courier'],
            'address' => ['nullable', 'array'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.house' => ['nullable', 'string', 'max:64'],
            'address.entrance' => ['nullable', 'string', 'max:64'],
            'address.floor' => ['nullable', 'string', 'max:64'],
            'address.apartment' => ['nullable', 'string', 'max:64'],
            'address.comment' => ['nullable', 'string', 'max:1000'],
            'address.lat' => ['nullable', 'numeric'],
            'address.lng' => ['nullable', 'numeric'],
            'scheduledAt' => ['nullable', 'string', 'max:64'],
            'client' => ['required', 'array'],
            'client.kind' => ['required', 'string', 'in:guest,client'],
            'client.id' => ['nullable', 'integer', 'min:1'],
            'client.name' => ['nullable', 'string', 'max:255'],
            'client.phone' => ['nullable', 'string', 'max:32'],
            'client.email' => ['nullable', 'string', 'max:255'],
            'paymentMethod' => ['required', 'string', 'in:cash,card_courier,card_online'],
            'changeFrom' => ['nullable', 'integer', 'min:0'],
            'giftProductId' => ['nullable', 'integer', 'min:1'],
            'complementSelections' => ['nullable', 'array'],
            'promoCode' => ['nullable', 'string', 'max:64'],
        ]);

        $complementSelections = [];
        foreach ($validated['complementSelections'] ?? [] as $productId => $qty) {
            $complementSelections[(int) $productId] = (int) $qty;
        }

        try {
            $result = $useCase->execute(new QuoteOrderDto(
                lines: array_map(
                    static fn (array $line): array => [
                        'product_id' => (int) $line['product_id'],
                        'quantity' => (int) $line['quantity'],
                    ],
                    $validated['lines'],
                ),
                deliveryMethod: $validated['deliveryMethod'],
                client: $validated['client'],
                address: $validated['address'] ?? null,
                scheduledAt: $validated['scheduledAt'] ?? null,
                paymentMethod: $validated['paymentMethod'],
                changeFrom: isset($validated['changeFrom']) ? (int) $validated['changeFrom'] : null,
                giftProductId: isset($validated['giftProductId']) ? (int) $validated['giftProductId'] : null,
                complementSelections: $complementSelections,
                promoCode: $validated['promoCode'] ?? null,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function store(Request $request, CreateOrderUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'client_request_id' => ['required', 'string', 'max:128'],
            'quote_snapshot' => ['required', 'array'],
        ]);

        try {
            $result = $useCase->execute(new CreateOrderDto(
                clientRequestId: $validated['client_request_id'],
                quoteSnapshot: $validated['quote_snapshot'],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, 201);
    }
}
