<?php

namespace App\Providers;

use App\FrontPad\FrontPad;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Order::created(function (Order $order) {
            \Log::info('A new order has been created.', ['order_id' => $order->id]);
            $fp = new FrontPad();
            $fp->createOrder($order);
        });
    }
}
