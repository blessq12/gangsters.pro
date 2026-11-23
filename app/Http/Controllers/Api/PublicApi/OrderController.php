<?php

namespace App\Http\Controllers\Api\PublicApi;

use App\Application\Order\Checkout\CheckoutPayload;
use App\Application\Order\Checkout\CheckoutRulesChangedException;
use App\Application\Order\Command\CreateOrderUseCase;
use App\Application\Order\DTO\CreateOrderDto;
use App\Application\Order\Query\GetCheckoutContextUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

final class OrderController extends Controller
{
    public function context(Request $request, GetCheckoutContextUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'min:1'],
        ]);

        try {
            return response()->json($useCase->execute((int) $validated['branch_id']));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function store(
        Request $request,
        CreateOrderUseCase $useCase,
        GetCheckoutContextUseCase $context,
    ): JsonResponse {
        $validated = Validator::make(
            [
                ...CheckoutPayload::normalize($request->all()),
                'client_request_id' => $request->input('client_request_id'),
                'rules_version' => $request->input('rules_version'),
                'expected_total_kopecks' => $request->input('expected_total_kopecks'),
            ],
            [
                ...CheckoutPayload::rules(),
                'client_request_id' => ['required', 'string', 'max:128'],
                'rules_version' => ['required', 'string', 'max:64'],
                'expected_total_kopecks' => ['required', 'integer', 'min:0'],
            ],
        )->validate();

        try {
            $result = $useCase->execute(new CreateOrderDto(
                clientRequestId: $validated['client_request_id'],
                checkout: CheckoutPayload::toDto($validated),
                rulesVersion: $validated['rules_version'],
                expectedTotalKopecks: (int) $validated['expected_total_kopecks'],
            ));
        } catch (CheckoutRulesChangedException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'reason' => $exception->reason,
                'context' => $context->execute($exception->branchId),
            ], 409);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, 201);
    }
}
