<?php

namespace App\Http\Controllers\Api\PublicApi;

use App\Application\Marketing\Query\GetPublicMarketingUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class MarketingController extends Controller
{
    public function show(GetPublicMarketingUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute());
    }
}
