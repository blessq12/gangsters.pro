<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FrontpadService
{
    protected $api_secret;
    protected $api_url;
    protected $client;
    protected $appUrl;
    protected $setting;

    public function __construct()
    {
        $this->api_secret = env('FRONTPAD_API_SECRET');
        $this->api_url = env('FRONTPAD_API_URL');
        $this->client = new Client();
        $this->setting = \App\Models\Setting::first();
        $this->appUrl = env('APP_URL');
    }

    public function createOrder(Order $siteOrder)
    {
        $siteOrder = Order::find($siteOrder->id);
        $items = $siteOrder->items;

        $order = [
            'secret' => $this->api_secret,
            'product' => [],
            'product_kol' => [],
            'name' => $siteOrder->name,
            'phone' => $siteOrder->tel,
            'person' => $siteOrder->personQty,
            'descr' => $siteOrder->comment,
            'street' => $siteOrder->street ?? '',
            'home' => $siteOrder->house ?? '',
            'pod' => $siteOrder->staircase ?? '',
            'et' => $siteOrder->floor ?? '',
            'apart' => $siteOrder->apartment ?? '',
            // 'hook_url' => $this->appUrl . '/api/orders/update',
            'hook_status' => [1, 10, 11],
        ];

        foreach ($items as $item) {
            $order['product'][] = intval($item->sku);
            $order['product_kol'][] = intval($item->qty);
        }

        try {
            $response = $this->client->post($this->api_url . '?new_order', ['form_params' => $order]);
            $responseBody = json_decode($response->getBody()->getContents(), true);
            Log::debug('FrontPad API Response: ' . json_encode($responseBody));

            if ($responseBody['result'] === 'success') {
                $siteOrder->frontpad_id = $responseBody['order_id'];
                $siteOrder->save();
                Log::info("Order successfully created in FrontPad. Order ID: {$responseBody['order_id']}, SKUs: " . implode(', ', $order['product']));
            } else {
                Log::error("Failed to create order on FrontPad: " . json_encode($responseBody));
            }
        } catch (\Throwable $th) {
            Log::error("Error during create new order on FrontPad: {$th->getMessage()}");
            return 'Error during create new order on FrontPad. Error: ' . $th->getMessage();
        }
    }

    public function getProducts()
    {
        try {
            $response = $this->client->post($this->api_url . '?get_products', ['form_params' => ['secret' => $this->api_secret]]);
            $data = json_decode($response->getBody()->getContents());

            $result = [];
            foreach ($data->product_id as $k => $prod_id) {
                $result[] = [
                    'id' => $prod_id,
                    'name' => $data->name[$k],
                    'price' => $data->price[$k],
                    'sale' => $data->sale[$k] ? true : false,
                ];
            }

            return $result;
        } catch (\Throwable $th) {
            Log::error("Error fetching products from FrontPad: {$th->getMessage()}");
            return [];
        }
    }

    public function updateOrder(Order $siteOrder, $status)
    {
        if (!$siteOrder) {
            Log::error("Order not found: id = " . $siteOrder->id);
            return;
        }
        if ($this->setting->use_coin_system) {
            if ($siteOrder->user && $status == 10) {
                $user = \App\Models\User::find($siteOrder->user_id);
                Log::info("Order {$siteOrder->id} is auth by {$user->email}");
                $user->coins += round($siteOrder->total / 100 * $this->setting->coin_system_percent);
                Log::info("User {$user->email} get {$user->coins} coins");
                $user->save();
            }
        }

        $siteOrder->status = $status;
        $siteOrder->save();
        Log::info("Order updated: id = {$siteOrder->id}, status = {$siteOrder->status}");
    }
}
