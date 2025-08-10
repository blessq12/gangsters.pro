<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Facades\Frontpad;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrderController extends Controller
{

    protected $user = null;

    public function __construct()
    {
        $this->middleware('auth:sanctum', ['only' => [
            'getMyOrders',
            'getMyCoins'
        ]]);

        if (auth('sanctum')->user()) {
            $this->user = auth('sanctum')->user();
        }
    }

    public function getMyOrders()
    {
        $orders = auth('sanctum')->user()->orders;
        $orders->each(function ($order) {
            $order->created = Carbon::parse($order->created_at)->format('d.m.Y H:i');
            $order->status_text = Order::GET_STATUS[$order->status];
        });
        return response()->json($orders);
    }
    public function getMyCoins()
    {
        $coins = auth('sanctum')->user()->coins;
        return response()->json($coins);
    }

    /**
     * @param Request $request
     *
     */
    public function createOrder(Request $request)
    {
        $cart = [];
        foreach ($request->cart as $item) {
            $cart[] = $item['sku'];
        }

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
        $order->payType = $request->order['payType'] ?? 'cash';

        if ($request->delivery) {
            $order->street = $request->order['street'];
            $order->house = $request->order['house'] . ' ' . ($request->order['building'] ? 'строение ' . $request->order['building'] : '');
            $order->staircase = $request->order['staircase'];
            $order->floor = $request->order['floor'];
            $order->apartment = $request->order['apartment'];

            if ($request->order['saveAddress']) {
                $this->user->addresses()->updateOrInsert([
                    'user_id' => $this->user->id,
                    'street' => $request->order['street'],
                    'house' => $request->order['house'],
                    'building' => $request->order['building'],
                    'staircase' => $request->order['staircase'],
                    'floor' => $request->order['floor'],
                    'apartment' => $request->order['apartment'],
                ]);
            }
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
            return response('', 200);
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

    public function checkAvalibility(Request $request)
    {
        $productIds = $request->ids;
        if (!$productIds) throw new \Exception('No products provided', 422);
        $unavailableIds = [];

        foreach ($productIds as $id) {
            $product = Product::find($id);
            if (!$product || !$product->visible) {
                $unavailableIds[] = $id;
            }
        }

        if (!empty($unavailableIds)) {
            return response()->json($unavailableIds, 422);
        }

        return response()->json([], 200);
    }
}
