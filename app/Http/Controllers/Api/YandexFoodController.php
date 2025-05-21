<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Yandex\YandexFoodOrderService;
use App\Services\Yandex\YandexFoodMenuService;

class YandexFoodController extends Controller
{
    private $orderService;
    private $menuService;

    public function __construct(
        YandexFoodOrderService $orderService,
        YandexFoodMenuService $menuService
    ) {
        $this->orderService = $orderService;
        $this->menuService = $menuService;
        $this->middleware('yandexAuth')->except('login');
    }

    public function login(Request $request)
    {
        $clientId = $request->client_id;
        $clientSecret = $request->client_secret;

        if (!$clientId || !$clientSecret) {
            return response()->json([
                "code" => 100,
                "description" => "Client ID and Client Secret are required"
            ], 400);
        }

        if ($clientId !== env('YANDEX_CLIENT_ID') || $clientSecret !== env('YANDEX_CLIENT_SECRET')) {
            return response()->json([
                "code" => 100,
                "description" => "Invalid client ID or client secret"
            ], 400);
        }

        if (empty(env('YANDEX_CLIENT_ID')) || empty(env('YANDEX_CLIENT_SECRET'))) {
            return response()->json([
                "code" => 100,
                "description" => "Client ID or Client Secret are not set in app config"
            ], 400);
        }

        return response()->json([
            "access_token" => env('YANDEX_EDA_AUTH_TOKEN')
        ], 200);
    }

    public function getMenu(string $id)
    {
        return $this->menuService->getMenu($id);
    }

    public function getMenuComposition(string $id)
    {
        return $this->menuService->getMenuComposition($id);
    }

    public function getMenuAvailability(string $id)
    {
        return $this->menuService->getMenuAvailability($id);
    }

    public function getMenuPromos(string $id)
    {
        return $this->menuService->getMenuPromos($id);
    }

    public function createOrder(Request $request)
    {
        return $this->orderService->createOrder($request->all());
    }

    public function getOrderById(string $id)
    {
        return $this->orderService->getOrderById($id);
    }

    public function getOrderStatus(string $id)
    {
        return $this->orderService->getOrderStatus($id);
    }

    public function updateOrder(Request $request, string $id)
    {
        return $this->orderService->updateOrder($request, $id);
    }

    public function deleteOrder(string $id)
    {
        return $this->orderService->deleteOrder($id);
    }

    public function getRestaurants()
    {
        return response()->json([
            "places" => [
                [
                    "id" => "1",
                    "title" => \App\Models\Company::first()->name,
                    "address" => \App\Models\Company::first()->city . ", " . \App\Models\Company::first()->street . ", " . \App\Models\Company::first()->house
                ]
            ]

        ], 200);
    }
}
