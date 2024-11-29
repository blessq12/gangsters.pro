<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\FrontpadException;

class FrontpadService
{
    protected $api_secret;
    protected $api_url;
    protected $client;
    protected $appUrl;
    protected $setting;
    protected $maxRetries = 3;

    public function __construct()
    {
        $this->api_secret = env('FRONTPAD_API_SECRET');
        $this->api_url = env('FRONTPAD_API_URL');
        $this->client = new Client([
            'timeout' => 10,
            'connect_timeout' => 5
        ]);
        $this->setting = Setting::first();
        $this->appUrl = env('APP_URL');
    }

    /**
     * Создание заказа в FrontPad
     * @throws FrontpadException
     */
    public function createOrder(Order $siteOrder)
    {
        try {
            Log::debug("Frontpad Facade input order: " . json_encode($siteOrder));

            // Валидация входных данных
            $this->validateOrder($siteOrder);

            $items = $siteOrder->items;

            // Формируем запрос в том же формате
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
                'hook_status' => [1, 10, 11],
            ];

            foreach ($items as $item) {
                $order['product'][] = intval($item->sku);
                $order['product_kol'][] = intval($item->qty);
            }

            Log::debug('order frontpad facade ' . json_encode($order));

            // Отправка запроса с retry механизмом
            $attempt = 1;
            do {
                try {
                    $response = $this->client->post($this->api_url . '?new_order', [
                        'form_params' => $order,
                        'timeout' => 10
                    ]);

                    Log::debug('request frontpad facade ' . json_encode($order));

                    $responseBody = json_decode($response->getBody()->getContents(), true);

                    Log::debug('responseBody frontpad facade ' . json_encode($responseBody));

                    if ($responseBody['result'] === 'success') {
                        $siteOrder->frontpad_id = $responseBody['order_id'];
                        $siteOrder->save();
                        return true;
                    }

                    throw new FrontpadException("Failed to create order on FrontPad: " . json_encode($responseBody));
                } catch (GuzzleException $e) {
                    if ($attempt === $this->maxRetries) {
                        throw new FrontpadException("Failed to create order after {$this->maxRetries} attempts: " . $e->getMessage());
                    }
                    Log::warning("Retry attempt {$attempt} failed: " . $e->getMessage());
                    sleep(1); // Пауза перед повторной попыткой
                }
                $attempt++;
            } while ($attempt <= $this->maxRetries);
        } catch (\Throwable $th) {
            Log::error("Error during create new order on FrontPad: {$th->getMessage()}");
            throw new FrontpadException("Failed to create order: " . $th->getMessage());
        }
    }

    /**
     * Валидация заказа
     * @throws FrontpadException
     */
    protected function validateOrder(Order $order)
    {
        $validator = Validator::make($order->toArray(), [
            'name' => 'required|string|max:255',
            'tel' => 'required|string|max:20',
            'personQty' => 'required|integer|min:1',
            'items' => 'required|array|min:1',
            'items.*.sku' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new FrontpadException("Order validation failed: " . json_encode($validator->errors()));
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
