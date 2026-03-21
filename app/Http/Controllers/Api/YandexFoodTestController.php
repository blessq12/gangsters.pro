<?php

namespace App\Http\Controllers\Api;

use App\Application\YandexFood\DTO\YandexAuthRequestDto;
use App\Application\YandexFood\DTO\YandexCreateOrderRequestDto;
use App\Application\YandexFood\DTO\YandexDeleteOrderRequestDto;
use App\Application\YandexFood\DTO\YandexMenuAvailabilityRequestDto;
use App\Application\YandexFood\DTO\YandexMenuCompositionRequestDto;
use App\Application\YandexFood\DTO\YandexMenuPromosRequestDto;
use App\Application\YandexFood\DTO\YandexMenuRequestDto;
use App\Application\YandexFood\DTO\YandexOrderIdRequestDto;
use App\Application\YandexFood\DTO\YandexRestaurantsRequestDto;
use App\Application\YandexFood\DTO\YandexUpdateOrderRequestDto;
use App\Application\YandexFood\YandexFoodApplicationFacade;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Временный транспорт для рефакторинга: тот же контракт URL, что у YandexFoodController.
 * Сценарии — через YandexFoodApplicationFacade (без «тест» в слое приложения).
 */
class YandexFoodTestController extends Controller
{
    public function __construct(
        private readonly YandexFoodApplicationFacade $yandexFood,
    ) {
    }

    public function login(Request $request): JsonResponse
    {
        $dto = new YandexAuthRequestDto(
            (string) $request->input('client_id', ''),
            (string) $request->input('client_secret', ''),
        );
        $result = $this->yandexFood->login($dto);

        return $this->jsonTransport($result);
    }

    public function getMenu(string $id): JsonResponse
    {
        return $this->jsonTransport($this->yandexFood->getMenu(new YandexMenuRequestDto($id)));
    }

    public function getMenuComposition(string $id): JsonResponse
    {
        return $this->jsonTransport(
            $this->yandexFood->getMenuComposition(new YandexMenuCompositionRequestDto($id)),
        );
    }

    public function getMenuAvailability(string $id): JsonResponse
    {
        return $this->jsonTransport(
            $this->yandexFood->getMenuAvailability(new YandexMenuAvailabilityRequestDto($id)),
        );
    }

    public function getMenuPromos(string $id): JsonResponse
    {
        return $this->jsonTransport(
            $this->yandexFood->getMenuPromos(new YandexMenuPromosRequestDto($id)),
        );
    }

    public function createOrder(Request $request): JsonResponse
    {
        $payload = $request->all();
        $result = $this->yandexFood->createOrder(new YandexCreateOrderRequestDto($payload));

        return $this->jsonTransport($result, ['payloadEcho' => $payload]);
    }

    public function getOrderById(string $id): JsonResponse
    {
        return $this->jsonTransport($this->yandexFood->getOrderById(new YandexOrderIdRequestDto($id)));
    }

    public function getOrderStatus(string $id): JsonResponse
    {
        return $this->jsonTransport($this->yandexFood->getOrderStatus(new YandexOrderIdRequestDto($id)));
    }

    public function updateOrder(Request $request, string $id): JsonResponse
    {
        $payload = $request->all();
        $result = $this->yandexFood->updateOrder(
            new YandexUpdateOrderRequestDto($id, $payload),
        );

        return $this->jsonTransport($result, ['payloadEcho' => $payload]);
    }

    public function deleteOrder(string $id): JsonResponse
    {
        return $this->jsonTransport(
            $this->yandexFood->deleteOrder(new YandexDeleteOrderRequestDto($id, $id)),
        );
    }

    public function getRestaurants(): JsonResponse
    {
        return $this->jsonTransport($this->yandexFood->getRestaurants(new YandexRestaurantsRequestDto()));
    }

    /**
     * @param  array{status: int, body: array<string, mixed>}  $result
     * @param  array<string, mixed>  $extraBody
     */
    private function jsonTransport(array $result, array $extraBody = []): JsonResponse
    {
        $body = $result['body'];
        if ($result['status'] < 400) {
            $body = array_merge($body, $extraBody, ['stub' => true]);
        }

        return response()->json($body, $result['status']);
    }
}
