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

        // Маппинг типов оплаты на коды API
        $paymentMapping = [
            'cash' => 1,  // Нал.
            'card' => 2,  // Б/нал.
        ];

        $order = [
            'secret' => $this->api_secret,
            'product' => [],
            'product_kol' => [],
            'product_mod' => [],  // Для модификаторов
            'product_price' => [],  // Если нужно устанавливать цены
            'score' => $siteOrder->score ?? 0,  // Баллы для оплаты
            'sale' => $siteOrder->sale_percent ?? 0,  // Скидка %
            'sale_amount' => $siteOrder->sale_amount ?? 0,  // Скидка суммой (только один тип)
            'card' => $siteOrder->card ?? '',
            'street' => $siteOrder->street ?? '',
            'home' => $siteOrder->house ?? '',
            'pod' => $siteOrder->staircase ?? '',
            'et' => $siteOrder->floor ?? '',
            'apart' => $siteOrder->apartment ?? '',
            'phone' => $siteOrder->tel ?? '',
            'mail' => $siteOrder->email ?? '',  // Если опция сохранения клиентов активна
            'descr' => mb_strlen($siteOrder->comment) > 100 ? mb_substr($siteOrder->comment, 0, 100) : $siteOrder->comment,
            'name' => $siteOrder->name ?? '',
            'pay' => $paymentMapping[$siteOrder->payType ?? 'cash'],  // Код типа оплаты: 1=наличные, 2=карта
            'certificate' => $siteOrder->certificate ?? '',
            'person' => min((int)$siteOrder->personQty ?? 1, 99),  // До 2 знаков
            'tags' => $siteOrder->tags ?? [],  // Массив кодов
            'hook_status' => $siteOrder->hook_status ?? [1, 10, 11],  // Массив до 5
            'hook_url' => $this->appUrl . '/api/orders/update',  // Для вебхука
            'channel' => $siteOrder->channel ?? '',  // Канал продаж
            'datetime' => $siteOrder->datetime ?? '',  // Формат YYYY-MM-DD HH:MM:SS
            'affiliate' => $siteOrder->affiliate ?? '',  // Филиал
            'point' => $siteOrder->point ?? '',  // Точка продаж
        ];

        foreach ($items as $key => $item) {
            $order['product'][$key] = intval($item->sku);
            $order['product_kol'][$key] = intval($item->qty);
            if (isset($item->parent_id)) {  // Предполагаем, что mod имеет parent_id; доработай если иначе
                $order['product_mod'][$key] = $item->parent_id;
            }
            if (isset($item->custom_price)) {  // Если цена устанавливается
                $order['product_price'][$key] = $item->custom_price;
            }
        }

        try {
            // Логируем что отправляем в Frontpad
            Log::info('FrontPad API Request Data:', $order);
            Log::info('Order payType from DB:', ['payType' => $siteOrder->payType]);

            $response = $this->client->post($this->api_url . '?new_order', ['form_params' => $order]);
            $responseBody = json_decode($response->getBody()->getContents(), true);
            Log::debug('FrontPad API Response: ' . json_encode($responseBody));

            if ($responseBody['result'] === 'success') {
                $siteOrder->frontpad_id = $responseBody['order_id'];
                $siteOrder->save();
                Log::info("Order created in FrontPad. ID: {$responseBody['order_id']}, Number: " . ($responseBody['order_number'] ?? 'N/A'));
                if (isset($responseBody['warnings'])) {
                    Log::warning("Warnings in order creation: " . json_encode($responseBody['warnings']));
                }
            } else {
                Log::error("Failed to create order: " . json_encode($responseBody));
            }
        } catch (\Throwable $th) {
            Log::error("Error creating order: {$th->getMessage()}");
            return 'Error: ' . $th->getMessage();
        }
    }

    public function getProducts()
    {
        try {
            $response = $this->client->post($this->api_url . '?get_products', ['form_params' => ['secret' => $this->api_secret]]);
            $data = json_decode($response->getBody()->getContents(), true);

            if ($data['result'] !== 'success') {
                Log::error("Error getting products: " . json_encode($data));
                return [];
            }

            $result = [];
            foreach ($data['product_id'] as $k => $prod_id) {
                $result[] = [
                    'id' => $prod_id,
                    'name' => $data['name'][$k],
                    'price' => $data['price'][$k],
                    'sale' => (bool)($data['sale'][$k] ?? 0),  // 1=true, 0=false
                ];
            }

            return $result;
        } catch (\Throwable $th) {
            Log::error("Error fetching products: {$th->getMessage()}");
            return [];
        }
    }

    public function updateOrder(Order $siteOrder, $status)
    {
        if (!$siteOrder) {
            Log::error("Order not found: id = " . $siteOrder->id);
            return;
        }
        if ($this->setting->use_coin_system && $status == 10 && $siteOrder->user) {
            $user = \App\Models\User::find($siteOrder->user_id);
            Log::info("Order {$siteOrder->id} auth by {$user->email}");
            $user->coins += round($siteOrder->total / 100 * $this->setting->coin_system_percent);
            Log::info("User {$user->email} got {$user->coins} coins");
            $user->save();
        }

        $siteOrder->status = $status;
        $siteOrder->save();
        Log::info("Order updated: id = {$siteOrder->id}, status = {$siteOrder->status}");
    }
}
