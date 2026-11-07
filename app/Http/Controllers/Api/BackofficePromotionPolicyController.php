<?php

namespace App\Http\Controllers\Api;

use App\Application\Order\Command\SavePromotionPolicyFormUseCase;
use App\Application\Order\Query\GetPromotionPolicyFormUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BackofficePromotionPolicyController extends Controller
{
    public function __construct(
        private readonly GetPromotionPolicyFormUseCase $policy,
        private readonly SavePromotionPolicyFormUseCase $save,
    ) {}

    public function show(): JsonResponse
    {
        try {
            return response()->json(['policy' => $this->policy->execute()]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gift_pickup_min_order_rubles' => ['required', 'integer', 'min:1'],
            'gift_courier_min_order_rubles' => ['required', 'integer', 'min:1'],
            'gift_courier_weekdays' => ['required', 'array', 'min:1'],
            'gift_courier_weekdays.*' => ['integer', 'between:1,7'],
            'gift_benefit_active' => ['required', 'boolean'],
            'delivery_free_threshold_rubles' => ['required', 'integer', 'min:0'],
            'delivery_outside_zone_surcharge_rubles' => ['required', 'integer', 'min:0'],
            'delivery_benefit_active' => ['required', 'boolean'],
            'complement_set_benefit_active' => ['required', 'boolean'],
            'complement_set_rolls_per_set' => ['required', 'integer', 'min:1'],
            'complement_set_max_sets' => ['required', 'integer', 'min:1'],
        ]);

        try {
            return response()->json(['policy' => $this->save->execute($validated)]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
