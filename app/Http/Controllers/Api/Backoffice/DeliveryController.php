<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Delivery\Command\CreateRouteSheetUseCase;
use App\Application\Delivery\Command\SaveDeliveryConfigurationUseCase;
use App\Application\Delivery\Command\SaveDeliveryZoneUseCase;
use App\Application\Delivery\Command\SaveDeliveryZonesUseCase;
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
use App\Application\Delivery\Query\ListAdminDeliveryZonesUseCase;
use App\Domain\Supporting\Staff\Enum\StaffRole;
use App\Http\Controllers\Controller;
use App\Infrastructure\Staff\Model\EmployeeModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class DeliveryController extends Controller
{
    public function show(Request $request, GetAdminDeliveryUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'min:1'],
        ]);

        try {
            return response()->json($useCase->execute((int) $validated['branch_id']));
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

    public function replaceZones(Request $request, SaveDeliveryZonesUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'min:1'],
            'zones' => ['required', 'array'],
            'zones.*.id' => ['nullable', 'string', 'max:64'],
            'zones.*.name' => ['required', 'string', 'max:255'],
            'zones.*.fee' => ['nullable', 'integer', 'min:0'],
            'zones.*.fee_rubles' => ['nullable', 'integer', 'min:0'],
            'zones.*.is_remote' => ['sometimes', 'boolean'],
            'zones.*.geometry' => ['required', 'array'],
            'zones.*.geometry.type' => ['required', 'string', 'in:Polygon,MultiPolygon'],
            'zones.*.geometry.coordinates' => ['required', 'array'],
        ]);

        try {
            return response()->json($useCase->execute(
                branchId: (int) $validated['branch_id'],
                zones: $validated['zones'],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function storeZone(Request $request, SaveDeliveryZoneUseCase $useCase): JsonResponse
    {
        return $this->saveZone($request, $useCase, null);
    }

    public function updateZone(Request $request, string $id, SaveDeliveryZoneUseCase $useCase): JsonResponse
    {
        return $this->saveZone($request, $useCase, $id);
    }

    public function listZones(Request $request, ListAdminDeliveryZonesUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'min:1'],
        ]);

        return response()->json($useCase->execute(
            branchId: (int) $validated['branch_id'],
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
        ));
    }

    public function listRouteSheets(Request $request, ListActiveRouteSheetsUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'min:1'],
            'offset' => ['sometimes', 'integer', 'min:0'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        try {
            return response()->json($useCase->execute(
                branchId: (int) $validated['branch_id'],
                offset: (int) ($validated['offset'] ?? 0),
                limit: (int) ($validated['limit'] ?? 20),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function showRouteSheet(Request $request, int $id, GetRouteSheetUseCase $useCase): JsonResponse
    {
        $forCourierId = null;
        $user = $request->user('sanctum');
        if ($user instanceof EmployeeModel && (string) $user->role === StaffRole::Courier->value) {
            $forCourierId = (int) $user->id;
        }

        try {
            return response()->json($useCase->execute($id, $forCourierId));
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
            'outcome' => ['required', 'string', 'in:delivered,written_off'],
            'reason' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['nullable', 'string', 'in:cash,card_courier,card_online'],
        ]);

        $user = $request->user();
        $authorId = $user instanceof EmployeeModel ? (int) $user->id : null;

        try {
            $result = $useCase->execute(new SettleStopDto(
                sheetId: $id,
                orderId: $orderId,
                outcome: $validated['outcome'],
                reason: $validated['reason'] ?? null,
                paymentMethod: isset($validated['payment_method'])
                    ? (string) $validated['payment_method']
                    : null,
                authorEmployeeId: $authorId,
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
            'branch_id' => ['required', 'integer', 'min:1'],
            'zones' => ['sometimes', 'array'],
            'zones.*.id' => ['required', 'string', 'max:64'],
            'zones.*.name' => ['required', 'string', 'max:255'],
            'zones.*.fee' => ['nullable', 'integer', 'min:0'],
            'zones.*.fee_rubles' => ['nullable', 'integer', 'min:0'],
            'zones.*.is_remote' => ['sometimes', 'boolean'],
            'zones.*.geometry' => ['sometimes', 'array'],
        ]);

        try {
            $result = $useCase->execute(new SaveDeliveryConfigurationDto(
                minOrderRubles: isset($validated['min_order_rubles'])
                    ? (int) $validated['min_order_rubles']
                    : null,
                averageDeliveryTimeMinutes: isset($validated['average_delivery_time_minutes'])
                    ? (int) $validated['average_delivery_time_minutes']
                    : null,
                branchId: (int) $validated['branch_id'],
                zones: array_key_exists('zones', $validated) ? $validated['zones'] : null,
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
            'branch_id' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'fee' => ['required', 'integer', 'min:0'],
            'is_remote' => ['sometimes', 'boolean'],
            'geometry' => [$id === null ? 'required' : 'nullable', 'array'],
            'geometry.type' => ['required_with:geometry', 'string', 'in:Polygon,MultiPolygon'],
            'geometry.coordinates' => ['required_with:geometry', 'array'],
        ]);

        try {
            $result = $useCase->execute(new SaveDeliveryZoneDto(
                branchId: (int) $validated['branch_id'],
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
