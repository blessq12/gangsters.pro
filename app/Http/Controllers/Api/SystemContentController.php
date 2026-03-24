<?php

namespace App\Http\Controllers\Api;

use App\Application\SystemContent\Query\GetSystemBannersUseCase;
use App\Application\SystemContent\Query\GetSystemCompanyLegalUseCase;
use App\Application\SystemContent\Query\GetSystemCompanyUseCase;
use App\Application\SystemContent\Query\GetSystemDocumentsUseCase;
use App\Application\SystemContent\Query\GetSystemPromotionsUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class SystemContentController extends Controller
{
    public function __construct(
        private readonly GetSystemBannersUseCase $getSystemBanners,
        private readonly GetSystemPromotionsUseCase $getSystemPromotions,
        private readonly GetSystemCompanyUseCase $getSystemCompany,
        private readonly GetSystemCompanyLegalUseCase $getSystemCompanyLegal,
        private readonly GetSystemDocumentsUseCase $getSystemDocuments,
    ) {
    }

    public function banners(): JsonResponse
    {
        return response()->json($this->getSystemBanners->execute());
    }

    public function promotions(): JsonResponse
    {
        return response()->json($this->getSystemPromotions->execute());
    }

    public function company(): JsonResponse
    {
        return response()->json($this->getSystemCompany->execute());
    }

    public function companyLegal(): JsonResponse
    {
        return response()->json($this->getSystemCompanyLegal->execute());
    }

    public function documents(): JsonResponse
    {
        return response()->json($this->getSystemDocuments->execute());
    }
}

