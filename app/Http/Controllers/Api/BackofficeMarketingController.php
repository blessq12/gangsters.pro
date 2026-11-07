<?php

namespace App\Http\Controllers\Api;

use App\Application\Content\Command\SaveBannerUseCase;
use App\Application\Content\Command\SaveStorePromotionUseCase;
use App\Application\Content\Query\ListMarketingUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final class BackofficeMarketingController extends Controller
{
    public function __construct(
        private readonly ListMarketingUseCase $marketing,
        private readonly SaveBannerUseCase $saveBanner,
        private readonly SaveStorePromotionUseCase $savePromotion,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->marketing->execute());
    }

    public function storeBanner(Request $request): JsonResponse
    {
        return $this->writeBanner($request, null);
    }

    public function updateBanner(Request $request, int $banner): JsonResponse
    {
        return $this->writeBanner($request, $banner);
    }

    public function storePromotion(Request $request): JsonResponse
    {
        return $this->writePromotion($request, null);
    }

    public function updatePromotion(Request $request, int $promotion): JsonResponse
    {
        return $this->writePromotion($request, $promotion);
    }

    private function writeBanner(Request $request, ?int $id): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
            'image_desktop' => [$id === null ? 'required' : 'nullable', 'file', 'image', 'max:8192'],
            'image_mobile' => [$id === null ? 'required' : 'nullable', 'file', 'image', 'max:8192'],
        ]);

        try {
            $saved = $this->saveBanner->execute(
                $id,
                $request->boolean('is_active'),
                $this->storeImage($request->file('image_desktop'), 'marketing/banners/desktop'),
                $this->storeImage($request->file('image_mobile'), 'marketing/banners/mobile'),
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['banner' => $saved], $id === null ? 201 : 200);
    }

    private function writePromotion(Request $request, ?int $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'image' => ['nullable', 'file', 'image', 'max:8192'],
        ]);

        try {
            $saved = $this->savePromotion->execute(
                $id,
                $validated['title'],
                $validated['body'] ?? null,
                $request->boolean('is_active'),
                $this->storeImage($request->file('image'), 'marketing/promotions'),
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['promotion' => $saved], $id === null ? 201 : 200);
    }

    private function storeImage(?UploadedFile $file, string $directory): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        return $file->store($directory, 'public');
    }
}
