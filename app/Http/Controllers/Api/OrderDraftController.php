<?php

namespace App\Http\Controllers\Api;

use App\Application\Order\OrderDraft\DTO\OrderDraftInput;
use App\Application\Order\OrderDraft\DTO\PlaceOrderInput;
use App\Application\Order\OrderDraft\useCases\PlaceOrderUseCase;
use App\Application\Order\OrderDraft\useCases\PreviewOrderDraftUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderDraft\PlaceOrderRequest;
use App\Http\Requests\OrderDraft\PreviewOrderDraftRequest;
use Illuminate\Http\JsonResponse;

final class OrderDraftController extends Controller
{
    public function __construct(
        private readonly PreviewOrderDraftUseCase $previewOrderDraft,
        private readonly PlaceOrderUseCase $placeOrder,
    ) {}

    public function preview(PreviewOrderDraftRequest $request): JsonResponse
    {
        return response()->json(
            $this->previewOrderDraft->execute($this->mapDraftInput($request)),
        );
    }

    public function store(PlaceOrderRequest $request): JsonResponse
    {
        return response()->json(
            $this->placeOrder->execute(
                new PlaceOrderInput(
                    clientRequestId: (string) $request->validated('client_request_id'),
                    draft: $this->mapDraftInput($request),
                ),
            ),
            201,
        );
    }

    private function mapDraftInput(PreviewOrderDraftRequest|PlaceOrderRequest $request): OrderDraftInput
    {
        $validated = $request->validated();
        $cart = is_array($validated['cart'] ?? null) ? $validated['cart'] : [];

        /** @var list<array{product_id: int, quantity: int, payload: array<string, mixed>|null}> $lines */
        $lines = [];
        foreach ($cart['lines'] ?? [] as $line) {
            if (! is_array($line)) {
                continue;
            }

            $lines[] = [
                'product_id' => (int) ($line['product_id'] ?? 0),
                'quantity' => (int) ($line['quantity'] ?? 0),
                'payload' => is_array($line['payload'] ?? null) ? $line['payload'] : null,
            ];
        }

        return new OrderDraftInput(
            cartLines: $lines,
            selectedGiftProductId: isset($cart['selected_gift_product_id'])
                ? (int) $cart['selected_gift_product_id']
                : null,
            client: is_array($validated['client'] ?? null) ? $validated['client'] : null,
            delivery: is_array($validated['delivery'] ?? null) ? $validated['delivery'] : null,
            payment: is_array($validated['payment'] ?? null) ? $validated['payment'] : null,
        );
    }
}
