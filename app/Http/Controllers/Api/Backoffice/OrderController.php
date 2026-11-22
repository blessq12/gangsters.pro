<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Order\Checkout\CheckoutPayload;
use App\Application\Order\Command\AcceptOrderUseCase;
use App\Application\Order\Command\CancelOrderUseCase;
use App\Application\Order\Command\CompletePickupOrderUseCase;
use App\Application\Order\Command\CreateOrderUseCase;
use App\Application\Order\Command\ReassignBranchUseCase;
use App\Application\Order\DTO\AcceptOrderDto;
use App\Application\Order\DTO\CreateOrderDto;
use App\Application\Order\DTO\ReassignBranchDto;
use App\Application\Order\Query\GetAdminOrderUseCase;
use App\Application\Order\Query\ListAdminOrdersUseCase;
use App\Domain\Core\Order\Enum\OrderStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

final class OrderController extends Controller
{
    public function index(Request $request, ListAdminOrdersUseCase $useCase): JsonResponse
    {
        $status = $request->query('status');
        $offset = (int) $request->query('offset', 0);
        $limit = (int) $request->query('limit', 20);

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
        $validated = Validator::make(
            [
                ...CheckoutPayload::normalize($request->all()),
                'client_request_id' => $request->input('client_request_id'),
            ],
            [
                ...CheckoutPayload::rules(),
                'client_request_id' => ['required', 'string', 'max:128'],
            ],
        )->validate();

        try {
            $result = $useCase->execute(new CreateOrderDto(
                clientRequestId: $validated['client_request_id'],
                checkout: CheckoutPayload::toDto($validated),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, 201);
    }

    public function accept(int $id, AcceptOrderUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute(new AcceptOrderDto(
                orderId: $id,
            )));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function reassignBranch(
        Request $request,
        int $id,
        ReassignBranchUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'min:1'],
        ]);

        try {
            return response()->json($useCase->execute(new ReassignBranchDto(
                orderId: $id,
                branchId: (int) $validated['branch_id'],
            )));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function changeStatus(
        Request $request,
        int $id,
        CancelOrderUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'target_status' => ['required', 'string', 'max:64'],
            'actor_role' => ['nullable', 'string', 'max:64'],
        ]);

        if ($validated['target_status'] !== OrderStatus::Cancelled->value) {
            return response()->json([
                'message' => 'Этот статус выставляется доменным событием. Через эту команду можно только отменить заказ.',
            ], 422);
        }

        try {
            $result = $useCase->execute($id);
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
