<?php

namespace App\Http\Controllers\Api;

use App\Application\Checkout\DTO\ConfirmCheckoutDto;
use App\Application\Checkout\DTO\CreateCheckoutDto;
use App\Application\Checkout\DTO\SetCheckoutClientDto;
use App\Application\Checkout\DTO\SetCheckoutDeliveryDto;
use App\Application\Checkout\DTO\SetCheckoutPaymentDto;
use App\Application\Checkout\DTO\UpdateCheckoutCartDto;
use App\Application\Checkout\useCases\ConfirmCheckoutUseCase;
use App\Application\Checkout\useCases\CreateCheckoutUseCase;
use App\Application\Checkout\useCases\SetCheckoutClientUseCase;
use App\Application\Checkout\useCases\SetCheckoutDeliveryUseCase;
use App\Application\Checkout\useCases\SetCheckoutPaymentUseCase;
use App\Application\Checkout\useCases\UpdateCheckoutCartUseCase;
use App\Domain\Checkout\Enum\DeliveryMethod;
use App\Domain\Checkout\Enum\PaymentMethod;
use App\Domain\Checkout\ValueObject\DeliveryAddress;
use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\SetCheckoutClientRequest;
use App\Http\Requests\Checkout\SetCheckoutDeliveryRequest;
use App\Http\Requests\Checkout\SetCheckoutPaymentRequest;
use App\Http\Requests\Checkout\UpdateCheckoutCartRequest;
use Illuminate\Http\JsonResponse;

final class CheckoutController extends Controller
{
    public function __construct(
        private readonly CreateCheckoutUseCase $создатьЧекаут,
        private readonly UpdateCheckoutCartUseCase $обновитьКорзинуЧекаута,
        private readonly SetCheckoutClientUseCase $установитьКлиентаЧекаута,
        private readonly SetCheckoutDeliveryUseCase $установитьДоставкуЧекаута,
        private readonly SetCheckoutPaymentUseCase $установитьОплатуЧекаута,
        private readonly ConfirmCheckoutUseCase $подтвердитьЧекаут,
    ) {}

    public function store(): JsonResponse
    {
        return response()->json(
            $this->создатьЧекаут->execute(new CreateCheckoutDto()),
            201,
        );
    }

    public function updateCart(UpdateCheckoutCartRequest $request, string $checkoutId): JsonResponse
    {
        return response()->json(
            $this->обновитьКорзинуЧекаута->execute(
                new UpdateCheckoutCartDto(
                    checkoutId: $checkoutId,
                    productId: (int) $request->validated('product_id'),
                    quantity: (int) $request->validated('quantity'),
                    payload: $request->validated('payload'),
                ),
            ),
        );
    }

    public function setClient(SetCheckoutClientRequest $request, string $checkoutId): JsonResponse
    {
        return response()->json(
            $this->установитьКлиентаЧекаута->execute(
                new SetCheckoutClientDto(
                    checkoutId: $checkoutId,
                    clientId: $request->validated('client_id'),
                    name: $request->validated('name'),
                    phone: $request->validated('phone'),
                    email: $request->validated('email'),
                ),
            ),
        );
    }

    public function setDelivery(SetCheckoutDeliveryRequest $request, string $checkoutId): JsonResponse
    {
        $addressPayload = $request->validated('address');

        return response()->json(
            $this->установитьДоставкуЧекаута->execute(
                new SetCheckoutDeliveryDto(
                    checkoutId: $checkoutId,
                    method: DeliveryMethod::from((string) $request->validated('method')),
                    address: is_array($addressPayload)
                        ? new DeliveryAddress(
                            street: (string) ($addressPayload['street'] ?? ''),
                            house: (string) ($addressPayload['house'] ?? ''),
                            entrance: isset($addressPayload['entrance']) ? (string) $addressPayload['entrance'] : null,
                            apartment: isset($addressPayload['apartment']) ? (string) $addressPayload['apartment'] : null,
                        )
                        : null,
                    comment: $request->validated('comment'),
                    scheduledAt: $request->validated('scheduled_at'),
                ),
            ),
        );
    }

    public function setPayment(SetCheckoutPaymentRequest $request, string $checkoutId): JsonResponse
    {
        return response()->json(
            $this->установитьОплатуЧекаута->execute(
                new SetCheckoutPaymentDto(
                    checkoutId: $checkoutId,
                    method: PaymentMethod::from((string) $request->validated('method')),
                    changeFromRubles: $request->validated('change_from_rubles'),
                ),
            ),
        );
    }

    public function confirm(string $checkoutId): JsonResponse
    {
        return response()->json(
            $this->подтвердитьЧекаут->execute(
                new ConfirmCheckoutDto(checkoutId: $checkoutId),
            ),
        );
    }
}
