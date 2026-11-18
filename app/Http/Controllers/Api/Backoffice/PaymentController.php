<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Payment\Command\MarkPaymentPaidUseCase;
use App\Application\Payment\Command\RefundPaymentUseCase;
use App\Application\Payment\Query\GetPaymentUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

final class PaymentController extends Controller
{
    public function show(int $id, GetPaymentUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function markPaid(int $id, MarkPaymentPaidUseCase $useCase): JsonResponse
    {
        try {
            $result = $useCase->execute($id);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function refund(int $id, RefundPaymentUseCase $useCase): JsonResponse
    {
        try {
            $result = $useCase->execute($id);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }
}
