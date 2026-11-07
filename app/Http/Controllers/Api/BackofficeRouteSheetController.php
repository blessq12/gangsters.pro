<?php

namespace App\Http\Controllers\Api;

use App\Application\Delivery\Command\CompleteRouteSheetUseCase;
use App\Application\Delivery\Command\ReleaseRouteSheetUseCase;
use App\Application\Delivery\Command\SettleRouteStopUseCase;
use App\Application\Delivery\Command\TakeRouteSheetUseCase;
use App\Application\Delivery\Exception\RouteSheetNotFound;
use App\Application\Delivery\Query\ListActiveRouteSheetsUseCase;
use App\Application\Order\Exception\AdminOrderNotFound;
use App\Application\Order\Exception\AdminOrderTransitionException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class BackofficeRouteSheetController extends Controller
{
    public function __construct(
        private readonly ListActiveRouteSheetsUseCase $listSheets,
        private readonly ReleaseRouteSheetUseCase $releaseSheet,
        private readonly TakeRouteSheetUseCase $takeSheet,
        private readonly SettleRouteStopUseCase $settleStop,
        private readonly CompleteRouteSheetUseCase $completeSheet,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'route_sheets' => $this->listSheets->execute(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer', 'min:1'],
        ]);

        try {
            $sheet = $this->releaseSheet->execute(array_map('intval', $validated['order_ids']));
        } catch (InvalidArgumentException|AdminOrderTransitionException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'route_sheet' => $this->listSheets->present($sheet),
        ], 201);
    }

    public function take(int $sheet): JsonResponse
    {
        return $this->mutate(fn () => $this->takeSheet->execute($sheet));
    }

    public function settle(Request $request, int $sheet, int $order): JsonResponse
    {
        $validated = $request->validate([
            'outcome' => ['required', 'string', 'in:delivered,failed'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        return $this->mutate(fn () => $this->settleStop->execute(
            $sheet,
            $order,
            $validated['outcome'],
            $validated['reason'] ?? null,
        ));
    }

    public function complete(int $sheet): JsonResponse
    {
        return $this->mutate(fn () => $this->completeSheet->execute($sheet));
    }

    /**
     * @param  callable(): \App\Domain\Delivery\Entity\RouteSheet  $action
     */
    private function mutate(callable $action): JsonResponse
    {
        try {
            $sheet = $action();
        } catch (RouteSheetNotFound|AdminOrderNotFound $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        } catch (InvalidArgumentException|AdminOrderTransitionException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'route_sheet' => $this->listSheets->present($sheet),
        ]);
    }
}
