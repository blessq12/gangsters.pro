<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        // Получаем заказы за сегодня
        $todayOrders = Order::whereDate('created_at', Carbon::today())->get();

        // Получаем новых пользователей за сегодня
        $todayUsers = User::whereDate('created_at', Carbon::today())->get();

        // Общее количество товаров
        $productsCount = Product::count();

        // Общее количество пользователей
        $usersCount = User::count();

        // Сводная статистика по дню
        $totalTodayOrders = $todayOrders->count();
        $totalTodayUsers = $todayUsers->count();

        // Суммарная стоимость заказов за день
        $totalTodayRevenue = $todayOrders->sum('total');

        return $content
            ->title('Дашборд')
            ->body(view('admin.dashboard', [
                'todayOrders' => $todayOrders,
                'todayUsers' => $todayUsers,
                'productsCount' => $productsCount,
                'usersCount' => $usersCount,
                'totalTodayOrders' => $totalTodayOrders,
                'totalTodayUsers' => $totalTodayUsers,
                'totalTodayRevenue' => $totalTodayRevenue
            ]));
    }
}
