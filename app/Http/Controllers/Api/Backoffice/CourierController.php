<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Delivery\Query\ListCourierRouteSheetsUseCase;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class CourierController extends Controller
{
    public function routeSheets(Request $request, ListCourierRouteSheetsUseCase $useCase): JsonResponse
    {
        $user = $request->user('sanctum');
        if (! $user instanceof User) {
            return response()->json(['message' => 'Требуется вход.'], 401);
        }

        try {
            return response()->json($useCase->execute((int) $user->id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
