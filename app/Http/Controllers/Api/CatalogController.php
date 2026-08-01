<?php

namespace App\Http\Controllers\Api;

use App\Application\Catalog\Query\GetCatalogUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class CatalogController extends Controller
{
    public function __construct(
        private readonly GetCatalogUseCase $getCatalog,
    ) {}

    public function show(): JsonResponse
    {
        return response()->json($this->getCatalog->execute());
    }
}
