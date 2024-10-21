<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void createOrder(Order $order)
 * @return void
 * @method static void updateOrder(Order $order, string $status)
 * @return void
 * @method static string getOrderStatus(int $orderId) 
 * @return string
 */
class Frontpad extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'frontpad';
    }
}
