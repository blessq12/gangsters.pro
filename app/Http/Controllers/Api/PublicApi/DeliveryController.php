<?php

namespace App\Http\Controllers\Api\PublicApi;

use App\Application\Delivery\Query\GetPublicDeliveryUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

final class DeliveryController extends Controller
{
    public function show(GetPublicDeliveryUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute());
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }
}
