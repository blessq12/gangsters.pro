<?php

namespace App\Http\Controllers\Api;

use App\Application\SystemContent\Query\GetSystemBannersUseCase;
use App\Application\SystemContent\Query\GetSystemPromotionsUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class SystemContentController extends Controller
{
    public function __construct(
        private readonly GetSystemBannersUseCase $getSystemBanners,
        private readonly GetSystemPromotionsUseCase $getSystemPromotions,
    ) {
    }

    public function banners(): JsonResponse
    {
        return response()->json($this->getSystemBanners->execute());
    }

    public function promotions(): JsonResponse
    {
        return response()->json($this->getSystemPromotions->execute());
    }
}

