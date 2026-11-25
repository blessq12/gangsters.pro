<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Order\Command\CancelOrderUseCase;
use App\Application\Production\Command\CompleteWorkUseCase;
use App\Application\Production\Command\MarkTicketLineDoneUseCase;
use App\Application\Production\Command\MarkTicketLinePackedUseCase;
use App\Application\Production\Command\StartWorkUseCase;
use App\Application\Production\DTO\CompleteWorkDto;
use App\Application\Production\DTO\MarkTicketLineDoneDto;
use App\Application\Production\DTO\MarkTicketLinePackedDto;
use App\Application\Production\DTO\StartWorkDto;
use App\Application\Production\Query\GetTicketUseCase;
use App\Application\Production\Query\ListQueueUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Staff\Model\EmployeeModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class ProductionController extends Controller
{
    public function queue(Request $request, ListQueueUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'min:1'],
            'scope' => ['sometimes', 'string', 'in:queued,active'],
        ]);

        $employeeId = $this->employeeId($request);
        if ($employeeId === null) {
            return response()->json(['message' => 'Нужна авторизация сотрудника.'], 401);
        }

        return response()->json($useCase->execute(
            branchId: (int) $validated['branch_id'],
            employeeId: $employeeId,
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
            scope: (string) ($validated['scope'] ?? 'queued'),
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

    public function start(Request $request, int $orderId, StartWorkUseCase $useCase): JsonResponse
    {
        $employeeId = $this->employeeId($request);
        if ($employeeId === null) {
            return response()->json(['message' => 'Нужна авторизация сотрудника.'], 401);
        }

        try {
            $result = $useCase->execute(new StartWorkDto(
                orderId: $orderId,
                employeeId: $employeeId,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function complete(Request $request, int $orderId, CompleteWorkUseCase $useCase): JsonResponse
    {
        $employeeId = $this->employeeId($request);
        if ($employeeId === null) {
            return response()->json(['message' => 'Нужна авторизация сотрудника.'], 401);
        }

        try {
            $result = $useCase->execute(new CompleteWorkDto(
                orderId: $orderId,
                employeeId: $employeeId,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function markLineDone(
        Request $request,
        int $orderId,
        int $lineId,
        MarkTicketLineDoneUseCase $useCase,
    ): JsonResponse {
        $employeeId = $this->employeeId($request);
        if ($employeeId === null) {
            return response()->json(['message' => 'Нужна авторизация сотрудника.'], 401);
        }

        try {
            $result = $useCase->execute(new MarkTicketLineDoneDto(
                orderId: $orderId,
                lineId: $lineId,
                employeeId: $employeeId,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function markLinePacked(
        Request $request,
        int $orderId,
        int $lineId,
        MarkTicketLinePackedUseCase $useCase,
    ): JsonResponse {
        $employeeId = $this->employeeId($request);
        if ($employeeId === null) {
            return response()->json(['message' => 'Нужна авторизация сотрудника.'], 401);
        }

        try {
            $result = $useCase->execute(new MarkTicketLinePackedDto(
                orderId: $orderId,
                lineId: $lineId,
                employeeId: $employeeId,
            ));
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

    private function employeeId(Request $request): ?int
    {
        $user = $request->user('sanctum');
        if (! $user instanceof EmployeeModel) {
            return null;
        }

        $id = (int) $user->id;

        return $id > 0 ? $id : null;
    }
}
