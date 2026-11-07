<?php

namespace App\Http\Controllers\Api;

use App\Application\Catalog\Query\GetAdminCatalogUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class BackofficeCatalogController extends Controller
{
    public function __construct(
        private readonly GetAdminCatalogUseCase $catalog,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->catalog->execute());
    }
}
