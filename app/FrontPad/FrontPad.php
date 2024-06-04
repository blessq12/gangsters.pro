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

    public function createOrder(Order $siteOrder)
    {
        $order = new \stdClass();
        // required secret for pass data
        $order->secret = $this->api_secret;

        // products with products qty
        $order->product =
            $siteOrder->items->map(function ($item) {
                return $item->sku;
            });
        $order->product_kol =
            $siteOrder->items->map(function ($item) {
                return $item->qty;
            });

        // client info 
        $order->name = $siteOrder->name;
        $order->phone = $siteOrder->tel;

        // transform order object to array
        $order = (array) $order;

        try {
            $res = $this->client->post($this->api_url . '?new_order', ['form_params' => $order]);
            \Log::debug('order created without server errors');
        } catch (\Throwable $th) {
            return 'Error during create new order on FrontPad. Error: ' . $th;
        }
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
