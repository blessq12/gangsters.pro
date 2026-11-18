<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Order\Command\AcceptOrderUseCase;
use App\Application\Order\Command\ChangeOrderStatusUseCase;
use App\Application\Order\Command\CompletePickupOrderUseCase;
use App\Application\Order\Command\CreateOrderUseCase;
use App\Application\Order\Command\ReassignKitchenUseCase;
use App\Application\Order\DTO\AcceptOrderDto;
use App\Application\Order\DTO\ChangeOrderStatusDto;
use App\Application\Order\DTO\CreateOrderDto;
use App\Application\Order\DTO\ReassignKitchenDto;
use App\Application\Order\Query\GetAdminOrderUseCase;
use App\Application\Order\Query\ListAdminOrdersUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class OrderController extends Controller
{
    public function index(Request $request, ListAdminOrdersUseCase $useCase): JsonResponse
    {
        $status = $request->query('status');
        $offset = (int) $request->query('offset', 0);
        $limit = (int) $request->query('limit', 50);

        return response()->json($useCase->execute(
            status: is_string($status) ? $status : null,
            offset: $offset,
            limit: $limit,
        ));
    }

    public function show(int $id, GetAdminOrderUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
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

    public function accept(Request $request, int $id, AcceptOrderUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'kitchen_id' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            return response()->json($useCase->execute(new AcceptOrderDto(
                orderId: $id,
                kitchenId: isset($validated['kitchen_id']) ? (int) $validated['kitchen_id'] : null,
            )));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function reassignKitchen(
        Request $request,
        int $id,
        ReassignKitchenUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'kitchen_id' => ['required', 'integer', 'min:1'],
        ]);

        try {
            return response()->json($useCase->execute(new ReassignKitchenDto(
                orderId: $id,
                kitchenId: (int) $validated['kitchen_id'],
            )));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function changeStatus(
        Request $request,
        int $id,
        ChangeOrderStatusUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'target_status' => ['required', 'string', 'max:64'],
            'actor_role' => ['nullable', 'string', 'max:64'],
        ]);

        try {
            $result = $useCase->execute(new ChangeOrderStatusDto(
                orderId: $id,
                targetStatus: $validated['target_status'],
                actorRole: $validated['actor_role'] ?? null,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function completePickup(int $id, CompletePickupOrderUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
