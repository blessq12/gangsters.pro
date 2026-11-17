<?php

namespace App\Http\Controllers\Api\PublicApi;

use App\Application\Catalog\Query\GetCatalogUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class CatalogController extends Controller
{
    public function show(GetCatalogUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute());
    }
}
