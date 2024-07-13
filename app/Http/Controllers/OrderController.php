<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
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
        $cart = [];
        Log::debug("order from front: " . json_encode($request->all()));
        foreach ($request->cart as $item) {
            $cart[] = $item['sku'];
        }
        Log::debug("cart from front:" . json_encode($cart));

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
        Log::debug("order for frontpad: " . json_encode($order));

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
        $items = [];
        foreach ($cart as $item) {
            $order->items()->create([
                'product_id' => $item['id'],
                'sku' => $item['sku'],
                'qty' => $item['qty'],
                'price' => $item['price'],
            ]);
            $items[] = $item['sku'];
        }
        Log::debug("added items to order: " . json_encode($items));

        return true;
    }

    public function updateOrder(Request $request)
    {
        Log::info("Update Order Request: " . json_encode($request->all()));
        $order = Order::where('frontpad_id', $request->order_id)->first();
        if (!$order) {
            Log::error("Order not found: id = {$request->order_id}");
            return response()->json(['message' => 'Order not found'], 404);
        }
        Frontpad::updateOrder($order, $request->status);
    }
    public function getLastOrders($id)
    {
        $orders = Order::where('user_id', $id)->orderBy('created_at', 'desc')->take(10)->get();
        $orders->each(function ($order) {
            $order->status_text = match ($order->status) {
                1 => 'Новый',
                10 => 'Оплачен',
                11 => 'Отменен',
            };
            $order->created = $order->created_at->format('d.m.Y H:i');
            $order->items = json_decode($order->items);
        });
        return response()->json($orders, 200);
    }
}
