<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Delivery\Query\ListCourierRouteSheetsUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Staff\Model\STF_Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class CourierController extends Controller
{
    public function routeSheets(Request $request, ListCourierRouteSheetsUseCase $useCase): JsonResponse
    {
        $user = $request->user('sanctum');
        if (! $user instanceof STF_Employee) {
            return response()->json(['message' => 'Требуется вход.'], 401);
        }

        try {
            return response()->json($useCase->execute(
                courierId: (int) $user->id,
                offset: (int) $request->query('offset', 0),
                limit: (int) $request->query('limit', 20),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
