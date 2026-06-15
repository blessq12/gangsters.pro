<?php

namespace App\Http\Controllers\Api;

use App\Application\Storefront\useCases\GetStorefrontBootstrapUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class StorefrontController extends Controller
{
    public function __construct(
        private readonly GetStorefrontBootstrapUseCase $bootstrap,
    ) {}

    public function bootstrap(): JsonResponse
    {
        return response()->json($this->bootstrap->execute());
    }
}
