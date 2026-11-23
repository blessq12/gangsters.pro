<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Order\Checkout\CheckoutPayload;
use App\Application\Order\Command\AcceptOrderUseCase;
use App\Application\Order\Command\AddOrderNoteUseCase;
use App\Application\Order\Command\CancelOrderUseCase;
use App\Application\Order\Command\CompletePickupOrderUseCase;
use App\Application\Order\Command\CreateOrderUseCase;
use App\Application\Order\DTO\AcceptOrderDto;
use App\Application\Order\DTO\CreateOrderDto;
use App\Application\Order\Query\GetAdminOrderUseCase;
use App\Application\Order\Query\ListAdminOrdersUseCase;
use App\Application\Order\Query\QuoteOrderUseCase;
use App\Domain\Core\Order\Enum\OrderStatus;
use App\Http\Controllers\Controller;
use App\Infrastructure\Staff\Model\STF_Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

final class OrderController extends Controller
{
    public function index(Request $request, ListAdminOrdersUseCase $useCase): JsonResponse
    {
        $status = $request->query('status');
        $q = $request->query('q');
        $branchId = $request->query('branch_id');
        $deliveryMethod = $request->query('delivery_method');
        $offset = (int) $request->query('offset', 0);
        $limit = (int) $request->query('limit', 20);

        return response()->json($useCase->execute(
            status: is_string($status) ? $status : null,
            offset: $offset,
            limit: $limit,
            q: is_string($q) ? $q : null,
            branchId: is_numeric($branchId) ? (int) $branchId : null,
            deliveryMethod: is_string($deliveryMethod) ? $deliveryMethod : null,
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

    public function quote(Request $request, QuoteOrderUseCase $useCase): JsonResponse
    {
        $validated = Validator::make(
            CheckoutPayload::normalize($request->all()),
            CheckoutPayload::rules(),
        )->validate();

        try {
            $result = $useCase->execute(CheckoutPayload::toDto($validated));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
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

    public function addNote(
        Request $request,
        int $id,
        AddOrderNoteUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:4000'],
        ]);

        $user = $request->user('sanctum');
        $authorId = $user instanceof STF_Employee ? (int) $user->id : null;

        try {
            $note = $useCase->execute($id, $validated['body'], $authorId);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['note' => $note], 201);
    }
}
