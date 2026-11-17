<?php

namespace App\Http\Controllers\Api\PublicApi;

use App\Application\Company\Query\GetPublicCompanyUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class CompanyController extends Controller
{
    public function show(GetPublicCompanyUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute());
    }
}
