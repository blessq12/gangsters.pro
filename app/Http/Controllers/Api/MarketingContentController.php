<?php

namespace App\Http\Controllers\Api;

use App\Application\MarketingContent\useCases\GetMarketingContentUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class MarketingContentController extends Controller
{
    public function __construct(
        private readonly GetMarketingContentUseCase $getMarketingContent,
    ) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->getMarketingContent->execute(),
        ]);
    }

    public function banners(): JsonResponse
    {
        $data = $this->getMarketingContent->execute();

        return response()->json([
            'data' => $data['banners'],
        ]);
    }

    public function promotions(): JsonResponse
    {
        $data = $this->getMarketingContent->execute();

        return response()->json([
            'data' => $data['promotions'],
        ]);
    }
}
