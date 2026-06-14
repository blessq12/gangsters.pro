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
        private readonly GetCompanyDataUseCase $получитьКомпанию,
        private readonly GetCompanyLegalDataUseCase $получитьЮрлицо,
        private readonly GetCompanyDocumentsUseCase $получитьДокументы,
    ) {}

    public function main(): JsonResponse
    {
        return response()->json($this->получитьКомпанию->execute());
    }

    public function legals(): JsonResponse
    {
        return response()->json($this->получитьЮрлицо->execute());
    }

    public function documents(): JsonResponse
    {
        return response()->json($this->получитьДокументы->execute());
    }
}
