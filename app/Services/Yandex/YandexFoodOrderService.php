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
}
