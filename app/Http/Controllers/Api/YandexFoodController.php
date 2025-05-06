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
        $this->middleware('setJson');
        $this->orderService = $orderService;
        $this->menuService = $menuService;
    }

    public function login(Request $request)
    {
        return 'login';
    }

    public function getMenu(string $id)
    {
        return $this->menuService->getMenu($id);
    }

    public function getMenuComposition(string $id)
    {
        // Логика для получения состава меню
    }

    public function getMenuAvailability(string $id)
    {
        // Логика для получения доступности меню
    }

    public function getMenuPromos(string $id)
    {
        // Логика для получения акций меню
    }

    public function createOrder(Request $request)
    {
        // Логика для создания заказа
    }

    public function getOrderById(string $id)
    {
        // Логика для получения заказа по ID
    }

    public function getOrderStatus(string $id)
    {
        // Логика для получения статуса заказа
    }

    public function updateOrder(Request $request, string $id)
    {
        // Логика для обновления заказа
    }

    public function deleteOrder(string $id)
    {
        // Логика для удаления заказа
    }

    public function getRestaurants()
    {
        // Логика для получения списка ресторанов
    }
}
