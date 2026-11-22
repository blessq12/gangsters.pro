<?php

namespace App\Http\Controllers\Api\PublicApi;

use App\Application\Order\Checkout\CheckoutPayload;
use App\Application\Order\Command\CreateOrderUseCase;
use App\Application\Order\DTO\CreateOrderDto;
use App\Application\Order\Query\QuoteOrderUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

final class OrderController extends Controller
{
    public function quote(Request $request, QuoteOrderUseCase $useCase): JsonResponse
    {
        $validated = Validator::make(
            CheckoutPayload::normalize($request->all()),
            CheckoutPayload::rules(),
        )->validate();

        try {
            $result = $useCase->execute(CheckoutPayload::toDto($validated));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function store(Request $request, CreateOrderUseCase $useCase): JsonResponse
    {
        $validated = Validator::make(
            [
                ...CheckoutPayload::normalize($request->all()),
                'client_request_id' => $request->input('client_request_id'),
            ],
            [
                ...CheckoutPayload::rules(),
                'client_request_id' => ['required', 'string', 'max:128'],
            ],
        )->validate();

        try {
            $result = $useCase->execute(new CreateOrderDto(
                clientRequestId: $validated['client_request_id'],
                checkout: CheckoutPayload::toDto($validated),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, 201);
    }
}
