<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Delivery\Command\CreateRouteSheetUseCase;
use App\Application\Delivery\Command\SaveDeliveryConfigurationUseCase;
use App\Application\Delivery\Command\SaveDeliveryZoneUseCase;
use App\Application\Delivery\Command\SettleStopUseCase;
use App\Application\Delivery\Command\TakeRouteSheetUseCase;
use App\Application\Delivery\DTO\CreateRouteSheetDto;
use App\Application\Delivery\DTO\SaveDeliveryConfigurationDto;
use App\Application\Delivery\DTO\SaveDeliveryZoneDto;
use App\Application\Delivery\DTO\SettleStopDto;
use App\Application\Delivery\DTO\TakeRouteSheetDto;
use App\Application\Delivery\Query\GetAdminDeliveryUseCase;
use App\Application\Delivery\Query\GetRouteSheetUseCase;
use App\Application\Delivery\Query\ListActiveRouteSheetsUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class DeliveryController extends Controller
{
    public function show(GetAdminDeliveryUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute());
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function store(Request $request, SaveDeliveryConfigurationUseCase $useCase): JsonResponse
    {
        return $this->saveConfiguration($request, $useCase, 201);
    }

    public function update(Request $request, SaveDeliveryConfigurationUseCase $useCase): JsonResponse
    {
        return $this->saveConfiguration($request, $useCase, 200);
    }

    public function storeZone(Request $request, SaveDeliveryZoneUseCase $useCase): JsonResponse
    {
        return $this->saveZone($request, $useCase, null);
    }

    public function updateZone(Request $request, string $id, SaveDeliveryZoneUseCase $useCase): JsonResponse
    {
        return $this->saveZone($request, $useCase, $id);
    }

    public function listRouteSheets(ListActiveRouteSheetsUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute());
    }

    public function showRouteSheet(int $id, GetRouteSheetUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function createRouteSheet(Request $request, CreateRouteSheetUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $result = $useCase->execute(new CreateRouteSheetDto(
                orderIds: array_map('intval', $validated['order_ids']),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, 201);
    }

    public function takeRouteSheet(Request $request, int $id, TakeRouteSheetUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'courier_id' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $result = $useCase->execute(new TakeRouteSheetDto(
                sheetId: $id,
                courierId: (int) $validated['courier_id'],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function settleStop(
        Request $request,
        int $id,
        int $orderId,
        SettleStopUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'outcome' => ['required', 'string', 'in:delivered,failed'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $result = $useCase->execute(new SettleStopDto(
                sheetId: $id,
                orderId: $orderId,
                outcome: $validated['outcome'],
                reason: $validated['reason'] ?? null,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    private function saveConfiguration(
        Request $request,
        SaveDeliveryConfigurationUseCase $useCase,
        int $status,
    ): JsonResponse {
        $validated = $request->validate([
            'min_order_rubles' => ['nullable', 'integer', 'min:0'],
            'average_delivery_time_minutes' => ['nullable', 'integer', 'min:0'],
            'kitchen' => ['sometimes', 'array'],
            'kitchen.city' => ['nullable', 'string', 'max:255'],
            'kitchen.street' => ['nullable', 'string', 'max:255'],
            'kitchen.house' => ['nullable', 'string', 'max:64'],
            'kitchen.comment' => ['nullable', 'string'],
            'kitchen.search_line' => ['nullable', 'string', 'max:255'],
            'zones' => ['sometimes', 'array'],
            'zones.*.id' => ['required', 'string', 'max:64'],
            'zones.*.name' => ['required', 'string', 'max:255'],
            'zones.*.fee' => ['nullable', 'integer', 'min:0'],
            'zones.*.fee_rubles' => ['nullable', 'integer', 'min:0'],
            'zones.*.is_remote' => ['sometimes', 'boolean'],
        ]);

        try {
            $result = $useCase->execute(new SaveDeliveryConfigurationDto(
                minOrderRubles: isset($validated['min_order_rubles'])
                    ? (int) $validated['min_order_rubles']
                    : null,
                averageDeliveryTimeMinutes: isset($validated['average_delivery_time_minutes'])
                    ? (int) $validated['average_delivery_time_minutes']
                    : null,
                kitchen: is_array($validated['kitchen'] ?? null) ? $validated['kitchen'] : [],
                zones: is_array($validated['zones'] ?? null) ? $validated['zones'] : [],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, $status);
    }

    private function saveZone(
        Request $request,
        SaveDeliveryZoneUseCase $useCase,
        ?string $id,
    ): JsonResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'fee' => ['required', 'integer', 'min:0'],
            'is_remote' => ['sometimes', 'boolean'],
            'geometry' => [$id === null ? 'required' : 'nullable', 'array'],
            'geometry.type' => ['required_with:geometry', 'string', 'in:Polygon,MultiPolygon'],
            'geometry.coordinates' => ['required_with:geometry', 'array'],
        ]);

        try {
            $result = $useCase->execute(new SaveDeliveryZoneDto(
                id: $id,
                name: $validated['name'],
                feeRubles: (int) $validated['fee'],
                isRemote: (bool) ($validated['is_remote'] ?? false),
                geometry: is_array($validated['geometry'] ?? null) ? $validated['geometry'] : null,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result['zone'], $result['created'] ? 201 : 200);
    }
}
