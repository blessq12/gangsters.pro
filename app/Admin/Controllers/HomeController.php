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
        $todayOrders = Order::whereDate('created_at', Carbon::today())->get();
        $todayUsers = User::whereDate('created_at', Carbon::today())->get();
        $productsCount = Product::count();
        $usersCount = User::count();
        $totalTodayOrders = $todayOrders->count();
        $totalTodayUsers = $todayUsers->count();
        $totalTodayRevenue = $todayOrders->sum('total');
        $yaMetrika =  \App\Facades\YaMetrika::getTodayStatistic() ?? null;

        return $content
            ->title('Дашборд')
            ->body(view('admin.dashboard', [
                'todayOrders' => $todayOrders,
                'todayUsers' => $todayUsers,
                'productsCount' => $productsCount,
                'usersCount' => $usersCount,
                'totalTodayOrders' => $totalTodayOrders,
                'totalTodayUsers' => $totalTodayUsers,
                'totalTodayRevenue' => $totalTodayRevenue,
                'yaMetrika' => $yaMetrika
            ]));
    }
}
