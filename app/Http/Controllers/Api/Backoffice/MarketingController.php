<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Marketing\Command\SaveBannerUseCase;
use App\Application\Marketing\Command\SaveDeliveryConditionsTextUseCase;
use App\Application\Marketing\Command\SavePromotionMediaUseCase;
use App\Application\Marketing\DTO\SaveBannerDto;
use App\Application\Marketing\DTO\SaveDeliveryConditionsTextDto;
use App\Application\Marketing\DTO\SavePromotionMediaDto;
use App\Application\Marketing\Query\GetAdminMarketingUseCase;
use App\Application\Marketing\Query\ListAdminBannersUseCase;
use App\Application\Marketing\Query\ListAdminPromotionsUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class MarketingController extends Controller
{
    public function show(GetAdminMarketingUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute());
    }

    public function listBanners(Request $request, ListAdminBannersUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute(
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
        ));
    }

    public function listPromotions(Request $request, ListAdminPromotionsUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute(
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
        ));
    }

    public function storeBanner(Request $request, SaveBannerUseCase $useCase): JsonResponse
    {
        return $this->saveBanner($request, $useCase, null);
    }

    public function updateBanner(Request $request, int $id, SaveBannerUseCase $useCase): JsonResponse
    {
        return $this->saveBanner($request, $useCase, $id);
    }

    public function storePromotion(Request $request, SavePromotionMediaUseCase $useCase): JsonResponse
    {
        return $this->savePromotion($request, $useCase, null);
    }

    public function updatePromotion(Request $request, int $id, SavePromotionMediaUseCase $useCase): JsonResponse
    {
        return $this->savePromotion($request, $useCase, $id);
    }

    public function saveDeliveryConditions(
        Request $request,
        SaveDeliveryConditionsTextUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'body' => ['required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        try {
            $result = $useCase->execute(new SaveDeliveryConditionsTextDto(
                id: isset($validated['id']) ? (int) $validated['id'] : null,
                body: $validated['body'],
                isActive: (bool) ($validated['is_active'] ?? true),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    private function saveBanner(
        Request $request,
        SaveBannerUseCase $useCase,
        ?int $id,
    ): JsonResponse {
        $validated = $request->validate([
            'is_active' => ['sometimes', 'boolean'],
            'image_desktop' => [$id === null ? 'required' : 'nullable', 'string', 'max:1024'],
            'image_mobile' => [$id === null ? 'required' : 'nullable', 'string', 'max:1024'],
        ]);

        try {
            $result = $useCase->execute(new SaveBannerDto(
                id: $id,
                isActive: (bool) ($validated['is_active'] ?? true),
                imageDesktop: $validated['image_desktop'] ?? null,
                imageMobile: $validated['image_mobile'] ?? null,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result['banner'], $result['created'] ? 201 : 200);
    }

    private function savePromotion(
        Request $request,
        SavePromotionMediaUseCase $useCase,
        ?int $id,
    ): JsonResponse {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:1024'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        try {
            $result = $useCase->execute(new SavePromotionMediaDto(
                id: $id,
                title: $validated['title'],
                body: $validated['body'] ?? null,
                image: $validated['image'] ?? null,
                isActive: (bool) ($validated['is_active'] ?? true),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result['promotion'], $result['created'] ? 201 : 200);
    }
}
