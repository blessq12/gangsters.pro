<?php

namespace App\Http\Controllers\Api\PublicApi;

use App\Application\Promotion\Query\GetPublicPromotionPolicyUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

final class PromotionController extends Controller
{
    public function show(GetPublicPromotionPolicyUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute());
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }
}
