<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Order\Command\CancelOrderUseCase;
use App\Application\Production\Command\CompleteWorkUseCase;
use App\Application\Production\Command\StartWorkUseCase;
use App\Application\Production\DTO\CompleteWorkDto;
use App\Application\Production\DTO\StartWorkDto;
use App\Application\Production\Query\GetTicketUseCase;
use App\Application\Production\Query\ListQueueUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class ProductionController extends Controller
{
    public function queue(Request $request, ListQueueUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'kitchen_id' => ['required', 'integer', 'min:1'],
        ]);

        return response()->json($useCase->execute(
            kitchenId: (int) $validated['kitchen_id'],
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
        ));
    }

    public function show(int $orderId, GetTicketUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($orderId));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function start(int $orderId, StartWorkUseCase $useCase): JsonResponse
    {
        try {
            $result = $useCase->execute(new StartWorkDto(orderId: $orderId));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function complete(int $orderId, CompleteWorkUseCase $useCase): JsonResponse
    {
        try {
            $result = $useCase->execute(new CompleteWorkDto(orderId: $orderId));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function cancel(int $orderId, CancelOrderUseCase $useCase): JsonResponse
    {
        try {
            $result = $useCase->execute($orderId);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }
}
