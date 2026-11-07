<?php

namespace App\Http\Controllers\Api;

use App\Application\Crm\Query\ListAdminClientsUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BackofficeClientController extends Controller
{
    public function __construct(
        private readonly ListAdminClientsUseCase $listClients,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->listClients->execute(
            max(1, (int) $request->query('page', 1)),
            (int) $request->query('per_page', 20),
        ));
    }
}
