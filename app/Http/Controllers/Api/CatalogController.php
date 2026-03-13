<?php

namespace App\Http\Controllers\Api;

use App\Application\Catalog\Query\GetCatalogTreeUseCase;
use App\Http\Controllers\Controller;

final class CatalogController extends Controller
{
    public function __construct(
        private readonly GetCatalogTreeUseCase $getCatalogTree,
    ) {
    }

    public function tree()
    {
        $catalog = $this->getCatalogTree->execute();

        return response()->json($catalog);
    }
}

