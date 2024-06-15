<?php

namespace App\Http\Controllers;

use App\FrontPad\FrontPad;
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

        if ($request->delivery) {
            $order->delivery = $request->delivery;
            $order->name = $request->order['name'];
            $order->tel = $request->order['tel'];
            $order->street = $request->order['street'];
            $order->house = $request->order['house'];
            $order->building = $request->order['building'];
            $order->staircase = $request->order['staircase'];
            $order->floor = $request->order['floor'];
            $order->apartment = $request->order['apartment'];
            $order->comment = $request->order['comment'] ?? null;
            $order->personQty = $request->order['personQty'] ?? 1;
            $order->payType = $request->order['payType'] ?? 'cash';
        } else {
            $order->delivery = $request->delivery;
            $order->name = $request->order['name'];
            $order->tel = $request->order['tel'];
        }

        if (auth('sanctum')->user()) {
            $order->user_id = auth('sanctum')->user()->id;
        }

        $order->total = $this->cartTotal($request->cart);

        if (!$order->save()) throw new \Exception('Ошибка при создании заказа');

        if (!$this->addCartItems($order, $request->cart)) throw new \Exception('Ошибка при добавлении товаров в заказ');

        // отправка нового заказа в FrontPad

        $this->sendToFrontPad($order);

        return response('Заказ успешно создан', 200);

        // return response()->json($order, 200);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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

    public function sendToFrontPad(Order $order)
    {
        $frontPad = new \App\FrontPad\FrontPad();
        $frontPad->createOrder($order);
    }
}
