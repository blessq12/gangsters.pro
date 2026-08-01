<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Bootstrap\GetAppBootstrapCriticalUseCase;
use App\Support\Bootstrap\GetAppBootstrapDeferredUseCase;
use App\Support\Bootstrap\GetAppBootstrapUseCase;
use Illuminate\Http\JsonResponse;

final class AppBootstrapController extends Controller
{
    public function __construct(
        private readonly GetAppBootstrapUseCase $bootstrap,
        private readonly GetAppBootstrapCriticalUseCase $bootstrapCritical,
        private readonly GetAppBootstrapDeferredUseCase $bootstrapDeferred,
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
