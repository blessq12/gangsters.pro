<?php

namespace App\Http\Controllers\Api;

use App\Application\Content\useCases\GetBootstrapUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * HTTP adapter for Content BC public snapshot.
 */
final class ContentController extends Controller
{
    public function __construct(
        private readonly GetBootstrapUseCase $getBootstrap,
    ) {}

    public function show(): JsonResponse
    {
        return response()->json($this->getBootstrap->execute());
    }
}
