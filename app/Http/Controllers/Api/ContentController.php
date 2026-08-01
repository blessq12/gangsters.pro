<?php

namespace App\Http\Controllers\Api;

use App\Application\Content\useCases\GetContentBootstrapUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class ContentController extends Controller
{
    public function __construct(
        private readonly GetContentBootstrapUseCase $bootstrap,
    ) {}

    public function bootstrap(): JsonResponse
    {
        return response()->json($this->bootstrap->execute());
    }
}
