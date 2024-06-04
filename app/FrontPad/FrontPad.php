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
        \Log::debug('transform order: id = ' . $siteOrder->id);
        // required secret for pass data
        $order['secret'] = $this->api_secret;

        // products with products qty
        $order['product'] = [];
        $order['product_kol'] = [];

        foreach ($siteOrder->items as $item) {
            $order['product'][] = intval($item->sku);
        }
        foreach ($siteOrder->items as $item) {
            $order['product_kol'][] = intval($item->qty);
        }

        \Log::debug($order['product']);
        \Log::debug($order['product_kol']);

        // client info 
        $order['name'] = $siteOrder->name;
        $order['phone'] = $siteOrder->tel;

        // try {
        //     $res = $this->client->post($this->api_url . '?new_order', ['form_params' => $order]);
        //     \Log::debug('order created without server errors');
        // } catch (\Throwable $th) {
        //     return 'Error during create new order on FrontPad. Error: ' . $th;
        // }

        return $order;
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
