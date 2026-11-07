<?php

namespace App\Http\Controllers\Api;

use App\Application\Content\Command\SaveDeliverySettingsUseCase;
use App\Application\Content\Query\GetDeliverySettingsUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BackofficeDeliverySettingsController extends Controller
{
    public function __construct(
        private readonly GetDeliverySettingsUseCase $settings,
        private readonly SaveDeliverySettingsUseCase $save,
    ) {}

    public function show(): JsonResponse
    {
        try {
            return response()->json(['delivery' => $this->settings->execute()]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'min_order_rubles' => ['nullable', 'integer', 'min:0'],
            'average_delivery_time_minutes' => ['nullable', 'integer', 'min:0'],
            'kitchen' => ['nullable', 'array'],
            'kitchen.city' => ['nullable', 'string', 'max:255'],
            'kitchen.street' => ['nullable', 'string', 'max:255'],
            'kitchen.house' => ['nullable', 'string', 'max:64'],
            'kitchen.comment' => ['nullable', 'string', 'max:500'],
            'kitchen.search_line' => ['nullable', 'string', 'max:500'],
            'zones' => ['present', 'array'],
            'zones.*.id' => ['required', 'string', 'max:64'],
            'zones.*.name' => ['required', 'string', 'max:255'],
            'zones.*.fee_rubles' => ['required', 'integer', 'min:0'],
            'zones.*.is_remote' => ['required', 'boolean'],
        ]);

        try {
            return response()->json(['delivery' => $this->save->execute($validated)]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
