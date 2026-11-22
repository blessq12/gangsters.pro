<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Promotion\Command\AssignProductPromotionRoleUseCase;
use App\Application\Promotion\Command\DeleteProductPromotionRoleUseCase;
use App\Application\Promotion\Command\SavePromotionPolicyUseCase;
use App\Application\Promotion\Command\SyncPromotionZonesUseCase;
use App\Application\Promotion\DTO\AssignProductPromotionRoleDto;
use App\Application\Promotion\DTO\SavePromotionPolicyDto;
use App\Application\Promotion\Query\GetAdminPromotionPolicyUseCase;
use App\Application\Promotion\Query\ListAdminPromotionRolesUseCase;
use App\Application\Promotion\Query\ListAdminPromotionZonesUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class PromotionController extends Controller
{
    public function show(Request $request, GetAdminPromotionPolicyUseCase $useCase): JsonResponse
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

    public function listRoles(Request $request, ListAdminPromotionRolesUseCase $useCase): JsonResponse
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

    public function listZones(Request $request, ListAdminPromotionZonesUseCase $useCase): JsonResponse
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

    public function store(Request $request, SavePromotionPolicyUseCase $useCase): JsonResponse
    {
        return $this->savePolicy($request, $useCase, 201);
    }

    public function update(Request $request, SavePromotionPolicyUseCase $useCase): JsonResponse
    {
        return $this->savePolicy($request, $useCase, 200);
    }

    public function assignRole(Request $request, AssignProductPromotionRoleUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'min:1'],
            'product_id' => ['required', 'integer', 'min:1'],
            'role' => ['required', 'string', 'in:roll,gift,complement'],
            'name' => ['required', 'string', 'max:255'],
            'price_kopecks' => ['required', 'integer', 'min:0'],
            'paid_twin_product_id' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            $result = $useCase->execute(new AssignProductPromotionRoleDto(
                branchId: (int) $validated['branch_id'],
                productId: (int) $validated['product_id'],
                role: $validated['role'],
                name: $validated['name'],
                priceKopecks: (int) $validated['price_kopecks'],
                paidTwinProductId: isset($validated['paid_twin_product_id'])
                    ? (int) $validated['paid_twin_product_id']
                    : null,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, 201);
    }

    public function destroyRole(Request $request, DeleteProductPromotionRoleUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'min:1'],
            'product_id' => ['required', 'integer', 'min:1'],
            'role' => ['required', 'string', 'in:roll,gift,complement'],
        ]);

        try {
            $useCase->execute((int) $validated['branch_id'], (int) $validated['product_id'], $validated['role']);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(null, 204);
    }

    public function syncZones(Request $request, SyncPromotionZonesUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'min:1'],
        ]);

        try {
            return response()->json($useCase->execute((int) $validated['branch_id']));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    private function savePolicy(
        Request $request,
        SavePromotionPolicyUseCase $useCase,
        int $status,
    ): JsonResponse {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'min:1'],
            'gift_pickup_min_order_kopecks' => ['required', 'integer', 'min:0'],
            'gift_courier_min_order_kopecks' => ['required', 'integer', 'min:0'],
            'gift_courier_weekdays' => ['sometimes', 'array'],
            'gift_courier_weekdays.*' => ['integer', 'min:1', 'max:7'],
            'gift_benefit_active' => ['required', 'boolean'],
            'delivery_free_threshold_kopecks' => ['required', 'integer', 'min:0'],
            'delivery_benefit_active' => ['required', 'boolean'],
            'complement_set_benefit_active' => ['required', 'boolean'],
            'complement_set_rolls_per_set' => ['required', 'integer', 'min:1'],
            'complement_set_max_sets' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $result = $useCase->execute(new SavePromotionPolicyDto(
                branchId: (int) $validated['branch_id'],
                giftPickupMinOrderKopecks: (int) $validated['gift_pickup_min_order_kopecks'],
                giftCourierMinOrderKopecks: (int) $validated['gift_courier_min_order_kopecks'],
                giftCourierWeekdays: is_array($validated['gift_courier_weekdays'] ?? null)
                    ? $validated['gift_courier_weekdays']
                    : [],
                giftBenefitActive: (bool) $validated['gift_benefit_active'],
                deliveryFreeThresholdKopecks: (int) $validated['delivery_free_threshold_kopecks'],
                deliveryBenefitActive: (bool) $validated['delivery_benefit_active'],
                complementSetBenefitActive: (bool) $validated['complement_set_benefit_active'],
                complementSetRollsPerSet: (int) $validated['complement_set_rolls_per_set'],
                complementSetMaxSets: (int) $validated['complement_set_max_sets'],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, $status);
    }
}
