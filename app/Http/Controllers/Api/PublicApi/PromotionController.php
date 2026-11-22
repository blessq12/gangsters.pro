<?php

namespace App\Http\Controllers\Api\PublicApi;

use App\Application\Promotion\Query\GetPublicPromotionPolicyUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class PromotionController extends Controller
{
    public function show(Request $request, GetPublicPromotionPolicyUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'min:1'],
        ]);

        try {
            return response()->json($useCase->execute((int) $validated['branch_id']));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }
}
