<?php

namespace App\Http\Controllers\Api;

use App\Application\MarketingContent\useCases\GetMarketingContentUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class MarketingContentController extends Controller
{
    public function __construct(
        private readonly GetMarketingContentUseCase $получитьКонтент,
    ) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->получитьКонтент->execute(),
        ]);
    }

    public function banners(): JsonResponse
    {
        $data = $this->получитьКонтент->execute();

        return response()->json([
            'data' => $data['banners'],
        ]);
    }

    public function promotions(): JsonResponse
    {
        $data = $this->получитьКонтент->execute();

        return response()->json([
            'data' => $data['promotions'],
        ]);
    }
}
