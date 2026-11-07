<?php

namespace App\Http\Controllers\Api;

use App\Application\Order\Command\ChangeAdminOrderStatusUseCase;
use App\Application\Order\Command\PlaceAdminOrderUseCase;
use App\Application\Order\DTO\PlaceAdminOrderDto;
use App\Application\Order\Exception\AdminOrderNotFound;
use App\Application\Order\Exception\AdminOrderTransitionException;
use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Application\Order\Query\ListAdminOrdersUseCase;
use App\Http\Controllers\Controller;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class BackofficeOrderController extends Controller
{
    public function __construct(
        private readonly ListAdminOrdersUseCase $listOrders,
        private readonly PlaceAdminOrderUseCase $placeOrder,
        private readonly ChangeAdminOrderStatusUseCase $changeStatus,
        private readonly AdminOrderPresenter $presenter,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = (int) $request->query('per_page', 20);
        $status = $request->query('status');

        return response()->json($this->listOrders->execute(
            $page,
            $perPage,
            is_string($status) ? $status : null,
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'delivery_method' => ['required', 'string', 'in:pickup,courier'],
            'address' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'string', 'in:cash,card_courier,card_online'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'integer', 'min:1'],
            'lines.*.qty' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $order = $this->placeOrder->execute(new PlaceAdminOrderDto(
                clientName: $validated['client_name'],
                phone: $validated['phone'],
                deliveryMethod: $validated['delivery_method'],
                address: (string) ($validated['address'] ?? ''),
                paymentMethod: $validated['payment_method'],
                lines: array_map(
                    static fn (array $line): array => [
                        'product_id' => (int) $line['product_id'],
                        'qty' => (int) $line['qty'],
                    ],
                    $validated['lines'],
                ),
                createdAt: new DateTimeImmutable(),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'order' => $this->presenter->present($order),
        ], 201);
    }

    public function updateStatus(Request $request, int $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:confirmed,cancelled,delivered,in_transit,preparing,ready'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $updated = $this->changeStatus->execute(
                $order,
                $validated['status'],
                $validated['reason'] ?? null,
            );
        } catch (AdminOrderNotFound $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        } catch (AdminOrderTransitionException|InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'order' => $this->presenter->present($updated),
        ]);
    }
}
