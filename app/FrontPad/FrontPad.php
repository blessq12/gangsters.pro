<?php

namespace App\FrontPad;

use App\Models\Order;
use App\Models\Test;
use GuzzleHttp\Client;

class FrontPad
{

    protected $api_secret;
    protected $api_url;
    protected $client;

    public function __construct()
    {
        $this->api_secret = env('FRONTPAD_API_SECRET');
        $this->api_url = env('FRONTPAD_API_URL');
        $this->client = new Client();
    }

    public function createOrder(Order $order)
    {
        // $order = [
        //     'secret' => $this->api_secret,
        //     'product' => $order->items->map(function ($item) {
        //         return $item->id;
        //     }),
        //     'product_kol' => $order->items->map(function ($item) {
        //         return $item->qty;
        //     })
        // ];
        // \Log::debug('Created new Order online');

        // try {
        //     $this->client->post($this->api_url . '?new_order');
        //     \Log::debug('New order sended to frontPad successefully');
        // } catch (\Throwable $th) {
        //     \Log::debug('Error during execute creating new order (FrontPad). Error: ' . $th);
        // }
        return true;
    }

    public function getProducts()
    {
        $response = $this->client->post($this->api_url . '?get_products', ['form_params' => ['secret' => $this->api_secret]]);
        $response = json_decode($response->getBody()->getContents());

        $result = [];
        foreach ($response->product_id as $k => $prod_id) {
            $result[] = [
                'id' => $prod_id,
                'name' => $response->name[$k],
                'price' => $response->price[$k],
                'sale' => $response->sale[$k] ? true : false,
            ];
        }

        return $result;
    }
}
