<?php

namespace App\Http\Controllers;

use App\Facades\Frontpad;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    protected $user = null;

    public function __construct()
    {
        if (auth('sanctum')->user()) {
            $this->user = auth('sanctum')->user();
        }
    }

    /**
     * @param Request $request
     * 
     */
    public function createOrder(Request $request)
    {
        $order = new Order();
        $this->setOrderDetails($order, $request);

        $order->user_id = $this->user ? $this->user->id : null;
        $order->total = $this->cartTotal($request->cart);

        if (!$order->save()) {
            return response('Ошибка при создании заказа', 500);
        }

        if (!$this->addCartItems($order, $request->cart)) {
            return response('Ошибка при добавлении товаров в заказ', 500);
        }

        Frontpad::createOrder($order);
        return response('Заказ успешно создан', 200);
    }

    private function setOrderDetails(Order $order, Request $request)
    {
        $order->delivery = $request->delivery;
        $order->name = $request->order['name'];
        $order->tel = $request->order['tel'];
        $order->comment = $request->order['comment'] ?? null;
        $order->personQty = $request->order['personQty'] ?? 1;

        if ($request->delivery) {
            $order->street = $request->order['street'];
            $order->house = $request->order['house'];
            $order->building = $request->order['building'];
            $order->staircase = $request->order['staircase'];
            $order->floor = $request->order['floor'];
            $order->apartment = $request->order['apartment'];
        }
    }

    private function cartTotal(array $cart)
    {
        $cartTotal = 0;
        foreach ($cart as $item) {
            $cartTotal += $item['price'] * $item['qty'];
            // return $item;
        }
        return $cartTotal;
    }
    /**
     * @param Order $order
     * @param array $cart
     */
    private function addCartItems(Order $order, array $cart)
    {

        if (!$order) return false;
        if (empty($cart)) return false;

        foreach ($cart as $item) {
            $order->items()->create([
                'product_id' => $item['id'],
                'sku' => $item['sku'],
                'qty' => $item['qty'],
                'price' => $item['price'],
            ]);
        }

        return true;
    }

    public function updateOrder(Request $request)
    {
        $order = Order::where('frontpad_id', $request->order_id)->first();
        if (!$order) {
            Log::error("Order not found: id = {$request->order_id}");
            return response()->json(['message' => 'Order not found'], 404);
        }
        Frontpad::updateOrder($order);
    }
}
