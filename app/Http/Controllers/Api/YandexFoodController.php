<?php

namespace App\Http\Controllers\Api;

use App\Application\YandexFood\Command\CreateYandexFoodOrderUseCase;
use App\Application\YandexFood\Command\DeleteYandexFoodOrderUseCase;
use App\Application\YandexFood\Command\UpdateYandexFoodOrderUseCase;
use App\Application\YandexFood\DTO\YandexCreateOrderRequestDto;
use App\Application\YandexFood\DTO\YandexDeleteOrderRequestDto;
use App\Application\YandexFood\DTO\YandexMenuAvailabilityRequestDto;
use App\Application\YandexFood\DTO\YandexMenuCompositionRequestDto;
use App\Application\YandexFood\DTO\YandexMenuPromosRequestDto;
use App\Application\YandexFood\DTO\YandexOrderIdRequestDto;
use App\Application\YandexFood\DTO\YandexUpdateOrderRequestDto;
use App\Application\YandexFood\Query\GetYandexFoodMenuAvailabilityUseCase;
use App\Application\YandexFood\Query\GetYandexFoodMenuCompositionUseCase;
use App\Application\YandexFood\Query\GetYandexFoodMenuPromosUseCase;
use App\Application\YandexFood\Query\GetYandexFoodOrderByIdUseCase;
use App\Application\YandexFood\Query\GetYandexFoodOrderStatusUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\SystemContent\Model\SYS_Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * HTTP-слой интеграции Яндекс Еда: сценарии через Application use case’ы и ACL-презентер контракта API.
 */
class YandexFoodController extends Controller
{
    public function __construct(
        private readonly GetYandexFoodMenuCompositionUseCase $menuComposition,
        private readonly GetYandexFoodMenuAvailabilityUseCase $menuAvailability,
        private readonly GetYandexFoodMenuPromosUseCase $menuPromos,
        private readonly CreateYandexFoodOrderUseCase $createOrderUseCase,
        private readonly GetYandexFoodOrderByIdUseCase $orderById,
        private readonly GetYandexFoodOrderStatusUseCase $orderStatus,
        private readonly UpdateYandexFoodOrderUseCase $updateOrderUseCase,
        private readonly DeleteYandexFoodOrderUseCase $deleteOrderUseCase,
    ) {
        $this->middleware('yandexAuth')->except('login');
    }

    public function login(Request $request): JsonResponse
    {
        $clientId = $request->client_id;
        $clientSecret = $request->client_secret;

        if (!$clientId || !$clientSecret) {
            return response()->json([
                'code' => 100,
                'description' => 'Client ID and Client Secret are required',
            ], 400);
        }

        if ($clientId !== env('YANDEX_CLIENT_ID') || $clientSecret !== env('YANDEX_CLIENT_SECRET')) {
            return response()->json([
                'code' => 100,
                'description' => 'Invalid client ID or client secret',
            ], 400);
        }

        if (empty(env('YANDEX_CLIENT_ID')) || empty(env('YANDEX_CLIENT_SECRET'))) {
            return response()->json([
                'code' => 100,
                'description' => 'Client ID or Client Secret are not set in app config',
            ], 400);
        }

        return response()->json([
            'access_token' => env('YANDEX_EDA_AUTH_TOKEN'),
        ], 200);
    }

    public function getMenuComposition(string $id): JsonResponse
    {
        $body = $this->menuComposition->execute(new YandexMenuCompositionRequestDto($id));

        return response()->json($body);
    }

    public function getMenuAvailability(string $id): JsonResponse
    {
        $body = $this->menuAvailability->execute(new YandexMenuAvailabilityRequestDto($id));

        return response()->json($body);
    }

    public function getMenuPromos(string $id): JsonResponse
    {
        $body = $this->menuPromos->execute(new YandexMenuPromosRequestDto($id));

        return response()->json($body);
    }

    public function createOrder(Request $request): JsonResponse
    {
        $body = $this->createOrderUseCase->execute(
            new YandexCreateOrderRequestDto($request->all()),
        );

        $status = isset($body['code']) ? 400 : 200;

        return response()->json($body, $status);
    }

    public function getOrderById(string $id): JsonResponse
    {
        $body = $this->orderById->execute(new YandexOrderIdRequestDto($id));
        $status = isset($body['code']) ? 400 : 200;

        return response()->json($body, $status);
    }

    public function getOrderStatus(string $id): JsonResponse
    {
        $body = $this->orderStatus->execute(new YandexOrderIdRequestDto($id));
        $status = isset($body['code']) ? 400 : 200;

        return response()->json($body, $status);
    }

    public function updateOrder(Request $request, string $id): JsonResponse
    {
        $body = $this->updateOrderUseCase->execute(
            new YandexUpdateOrderRequestDto($id, $request->all()),
        );
        $status = isset($body['code']) ? 400 : 200;

        return response()->json($body, $status);
    }

    public function deleteOrder(Request $request, string $id): JsonResponse
    {
        $body = $this->deleteOrderUseCase->execute(
            new YandexDeleteOrderRequestDto(
                $id,
                $id,
                $request->input('eatsId'),
            ),
        );
        $status = isset($body['code']) ? 400 : 200;

        return response()->json($body, $status);
    }

    public function getRestaurants(): JsonResponse
    {
        $company = SYS_Company::query()->first();

        return response()->json([
            'places' => [
                [
                    'id' => '1',
                    'title' => $company?->name ?? '',
                    'address' => trim(($company?->city ?? '').', '.($company?->street ?? '').', '.($company?->house ?? ''), ', '),
                ],
            ],

        ], 200);
    }
}
