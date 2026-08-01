<?php

namespace App\Http\Controllers\Api;

use App\Application\Order\DTO\CreateOrderDto;
use App\Application\Order\DTO\QuoteOrderDto;
use App\Application\Order\Presenter\OrderPresenter;
use App\Application\Order\Command\CreateOrderUseCase;
use App\Application\Order\Query\QuoteOrderUseCase;
use App\Http\Controllers\Controller;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrderController extends Controller
{
    public function __construct(
        private readonly QuoteOrderUseCase $quoteOrder,
        private readonly CreateOrderUseCase $createOrder,
        private readonly OrderPresenter $orderPresenter,
    ) {}

    public function quote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'integer', 'min:1'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'delivery_method' => ['nullable', 'string', 'in:pickup,courier'],
            'client' => ['nullable', 'array'],
            'client.name' => ['nullable', 'string', 'max:255'],
            'client.phone' => ['nullable', 'string', 'max:32'],
            'client.email' => ['nullable', 'string', 'max:255'],
            'client.kind' => ['nullable', 'string', 'in:guest,registered'],
            'client.client_id' => ['nullable', 'integer', 'min:1'],
            'address' => ['nullable', 'array'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.house' => ['nullable', 'string', 'max:64'],
            'address.entrance' => ['nullable', 'string', 'max:64'],
            'address.apartment' => ['nullable', 'string', 'max:64'],
            'delivery_comment' => ['nullable', 'string', 'max:1000'],
            'scheduled_at' => ['nullable', 'string', 'max:64'],
            'payment_method' => ['nullable', 'string', 'max:32'],
            'change_from_rubles' => ['nullable', 'integer', 'min:0'],
            'gift_product_id' => ['nullable', 'integer', 'min:1'],
            'complement_product_ids' => ['nullable', 'array'],
            'complement_product_ids.*' => ['integer', 'min:1'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $client = is_array($validated['client'] ?? null) ? $validated['client'] : [];
        if (! isset($client['phone']) || ! is_string($client['phone']) || $client['phone'] === '') {
            $client['phone'] = '+7 (000) 000-00-00';
        }

        $quote = $this->quoteOrder->execute(new QuoteOrderDto(
            lines: array_map(
                static fn (array $line): array => [
                    'product_id' => (int) $line['product_id'],
                    'quantity' => (int) $line['quantity'],
                ],
                $validated['lines'],
            ),
            deliveryMethod: (string) ($validated['delivery_method'] ?? 'courier'),
            client: $client,
            address: is_array($validated['address'] ?? null) ? $validated['address'] : null,
            deliveryComment: $validated['delivery_comment'] ?? null,
            scheduledAt: $validated['scheduled_at'] ?? null,
            paymentMethod: (string) ($validated['payment_method'] ?? 'cash'),
            changeFromRubles: isset($validated['change_from_rubles'])
                ? (int) $validated['change_from_rubles']
                : null,
            giftProductId: isset($validated['gift_product_id'])
                ? (int) $validated['gift_product_id']
                : null,
            complementProductIds: array_map(
                'intval',
                $validated['complement_product_ids'] ?? [],
            ),
            latitude: isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            longitude: isset($validated['longitude']) ? (float) $validated['longitude'] : null,
        ));

        return response()->json(['data' => $quote]);
    }

    public function place(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_request_id' => ['required', 'string', 'max:128'],
            'cart' => ['required', 'array'],
            'cart.lines' => ['required', 'array', 'min:1'],
            'client' => ['required', 'array'],
            'delivery' => ['required', 'array'],
            'payment' => ['required', 'array'],
        ]);

        $order = $this->createOrder->execute(new CreateOrderDto(
            clientRequestId: (string) $validated['client_request_id'],
            cart: $validated['cart'],
            client: $validated['client'],
            delivery: $validated['delivery'],
            payment: $validated['payment'],
            createdAt: new DateTimeImmutable(),
        ));

        return response()->json([
            'data' => $this->orderPresenter->present($order),
        ], 201);
    }
}
