<?php

namespace App\Services\Yandex;

class YandexFoodOrderService
{
    public function createOrder(array $data)
    {
        return 'order created';
    }

    public function getOrderById(string $id)
    {
        return 'order data';
    }

    public function updateOrder(string $id, $data)
    {
        return 'order updated';
    }

    public function deleteOrder(string $id)
    {
        return 'order deleted';
    }

    public function getOrderStatus(string $id)
    {
        return 'order status';
    }

    public function getToken(string $clientId, string $clientSecret)
    {
        if (
            $clientId === env('YANDEX_CLIENT_ID') &&
            $clientSecret === env('YANDEX_CLIENT_SECRET')
        ) {
            return env('YANDEX_EDA_AUTH_TOKEN');
        }

        if (empty(env('YANDEX_CLIENT_ID')) || empty(env('YANDEX_CLIENT_SECRET'))) {
            return response()->json([
                "code" => 100,
                "description" => "Client ID or Client Secret are not set in app config"
            ], 400);
        }
    }
}
