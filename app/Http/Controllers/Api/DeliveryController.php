<?php

namespace App\Http\Controllers\Api;

use App\Application\Delivery\useCases\GetDeliveryDataUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class DeliveryController extends Controller
{
    public function __construct(
        private readonly GetDeliveryDataUseCase $получитьДанныеДоставки,
    ) {}

    public function show(): JsonResponse
    {
        return response()->json($this->получитьДанныеДоставки->execute());
    }
}
