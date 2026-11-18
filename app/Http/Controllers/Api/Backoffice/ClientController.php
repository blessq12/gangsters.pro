<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Crm\Query\GetAdminClientUseCase;
use App\Application\Crm\Query\ListAdminClientsUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class ClientController extends Controller
{
    public function index(Request $request, ListAdminClientsUseCase $useCase): JsonResponse
    {
        $offset = (int) $request->query('offset', 0);
        $limit = (int) $request->query('limit', 50);
        $q = $request->query('q');

        return response()->json($useCase->execute(
            offset: $offset,
            limit: $limit,
            q: is_string($q) ? $q : null,
        ));
    }

    public function show(int $id, GetAdminClientUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }
}
