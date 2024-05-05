<?php

namespace App\FrontPad;

use App\Models\Order;
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
        $this->client = new Client([
            // 'base_uri' => $this->api_url,
            // 'headers' => [
            //     'Content-Type' => 'application/json',
            //     'Authorization' => 'Bearer ' . $this->api_secret
            // ]
        ]);
    }
    public function newOrder(Order $order)
    {
        dd($order);
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
