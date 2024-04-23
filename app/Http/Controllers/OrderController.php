<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    protected $user = null;

    public function __construct()
    {
        if (auth('sanctum')->user()){
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
        if ($request->delivery){

            $order->delivery = $request->delivery;
            $order->name = $request->name;
            $order->tel = $request->tel;
            $order->street = $request->street;
            $order->house = $request->house;
            $order->building = $request->building;
            $order->staircase = $request->staircase;
            $order->floor = $request->floor;
            $order->apartment = $request->apartment;
            $order->total = $request->total;

        } else {

            $order->delivery = $request->delivery;
            $order->name = $request->name;
            $order->tel = $request->tel;

        }
        
        if (auth('sanctum')->user()){
            $order->user_id = auth('sanctum')->user()->id;
        } 

        $order->total = $this->cartTotal($request->cart);
        $order->cart = $request->cart;

        return response($order);
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
                'qty' => $item['qty'],
                'price' => $item['price'],
            ]);
        }

        return true;
    }
}
