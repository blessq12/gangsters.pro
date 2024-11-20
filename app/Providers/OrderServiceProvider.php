<?php

namespace App\Providers;

use App\FrontPad\FrontPad;
use App\Models\Order;
use App\Models\OrderItem;
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
        // OrderItem::created(function (Order $order) {
        //     $fp = new FrontPad();
        //     $fp->createOrder($order);
        // });
    }
}
