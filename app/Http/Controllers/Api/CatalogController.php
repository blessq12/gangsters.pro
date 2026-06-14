<?php

namespace App\Http\Controllers\Api;

use App\Application\Catalog\useCases\GetCatalogUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class CatalogController extends Controller
{
    public function __construct(
        private readonly GetCatalogUseCase $получитьКаталог,
    ) {}

    public function show(): JsonResponse
    {
        return response()->json($this->получитьКаталог->execute());
    }
}
