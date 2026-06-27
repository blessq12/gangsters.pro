<?php

namespace App\Http\Controllers\Api;

use App\Application\Storefront\useCases\GetStorefrontBootstrapCriticalUseCase;
use App\Application\Storefront\useCases\GetStorefrontBootstrapDeferredUseCase;
use App\Application\Storefront\useCases\GetStorefrontBootstrapUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class StorefrontController extends Controller
{
    public function __construct(
        private readonly GetStorefrontBootstrapUseCase $bootstrap,
        private readonly GetStorefrontBootstrapCriticalUseCase $bootstrapCritical,
        private readonly GetStorefrontBootstrapDeferredUseCase $bootstrapDeferred,
    ) {}

    public function bootstrap(): JsonResponse
    {
        return response()->json($this->bootstrap->execute());
    }

    public function bootstrapCritical(): JsonResponse
    {
        return response()->json($this->bootstrapCritical->execute());
    }

    public function bootstrapDeferred(): JsonResponse
    {
        return response()->json($this->bootstrapDeferred->execute());
    }
}
