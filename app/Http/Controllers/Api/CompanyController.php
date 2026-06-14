<?php

namespace App\Http\Controllers\Api;

use App\Application\Company\useCases\GetCompanyDataUseCase;
use App\Application\Company\useCases\GetCompanyDocumentsUseCase;
use App\Application\Company\useCases\GetCompanyLegalDataUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class CompanyController extends Controller
{
    public function __construct(
        private readonly GetCompanyDataUseCase $getCompanyData,
        private readonly GetCompanyLegalDataUseCase $getCompanyLegalData,
        private readonly GetCompanyDocumentsUseCase $getCompanyDocuments,
    ) {}

    public function main(): JsonResponse
    {
        return response()->json($this->getCompanyData->execute());
    }

    public function legals(): JsonResponse
    {
        return response()->json($this->getCompanyLegalData->execute());
    }

    public function documents(): JsonResponse
    {
        return response()->json($this->getCompanyDocuments->execute());
    }
}
