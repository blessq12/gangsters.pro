<?php

namespace App\Services\Yandex;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Facades\Frontpad;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class YandexFoodOrderService
{
    public function createOrder(array $data)
    {
        Log::info('YandexFoodOrderService::createOrder', ['data' => json_encode($data)]);

        try {
            $discriminator = $data['discriminator'];
            $eatsId = $data['eatsId'];
            $restaurantId = $data['restaurantId'];

            $clientName = $data['deliveryInfo']['clientName'];
            $phoneNumber = $data['deliveryInfo']['phoneNumber'];
            $deliveryDate = Carbon::parse($data['deliveryInfo']['deliveryDate'])->format('Y-m-d H:i:s');
            $deliveryAddress = $data['deliveryInfo']['deliveryAddress']['full'];
            $latitude = $data['deliveryInfo']['deliveryAddress']['latitude'];
            $longitude = $data['deliveryInfo']['deliveryAddress']['longitude'];

            $paymentType = $data['paymentInfo']['paymentType'];
            $itemsCost = $data['paymentInfo']['itemsCost'];
            $deliveryFee = $data['paymentInfo']['deliveryFee'];
            $total = $data['paymentInfo']['total'];
            $change = $data['paymentInfo']['change'];

            $items = $data['items'];
            $persons = $data['persons'];
            $comment = $data['comment'];
            $promos = json_encode($data['promos']);

            $order = new Order();
            $order->eatsId = $eatsId;
            $order->name = $clientName;
            $order->tel = $phoneNumber;
            $order->full_address = $deliveryAddress;
            $order->restaurantId = $restaurantId;
            $order->personQty = $persons;
            $order->comment = $comment;
            $order->latitude = $latitude;
            $order->longitude = $longitude;
            $order->deliveryDate = $deliveryDate;
            $order->deliveryType = $discriminator;
            $order->itemsCost = $itemsCost;
            $order->deliveryFee = $deliveryFee;
            $order->total = $total;
            $order->change = $change;
            $order->promos = $promos;
            $order->discriminator = $discriminator;

            if (!$order->save()) {
                return response()->json([
                    "code" => 100,
                    "description" => "Не удалось создать заказ"
                ], 400);
            }

            foreach ($items as $item) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $item['id'];
                $orderItem->qty = $item['quantity'];
                $orderItem->price = $item['price'];
                $orderItem->sku = Product::where('id', $item['id'])->first()->sku;

                if (isset($item['modifications'])) {
                    $orderItem->modifications = json_encode($item['modifications']);
                }

                $orderItem->save();
            }

            Frontpad::createOrder($order);

            return response()->json([
                'result' => 'OK',
                'orderId' => $order->id,
            ], 200);
        } catch (\Exception $e) {
            Log::error('YandexFoodOrderService::createOrder', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                "code" => 100,
                "description" => "Не удалось создать заказ"
            ], 400);
        }
    }

    public function getOrderById(string $id)
    {
        $order = Order::select('id', 'name', 'tel', 'full_address', 'restaurantId', 'personQty', 'comment', 'latitude', 'longitude', 'deliveryDate', 'deliveryType', 'itemsCost', 'deliveryFee', 'total', 'change', 'promos', 'discriminator')
            ->find($id);

        $order->items = $order->items->map(function ($item) {
            $item->modifications = [];
            $item->promos = [];
            $item->id = $item->product_id;
            $item->name = Product::where('id', $item->product_id)->first()->name;
            $item->quantity = $item->qty;
            unset($item->created_at);
            unset($item->updated_at);
            unset($item->product_id);
            unset($item->order_id);
            unset($item->sku);
            unset($item->qty);
            return $item;
        });

        if (!$order) {
            return response()->json([
                "code" => 100,
                "description" => "Заказ не найден"
            ], 400);
        }

        return response()->json([
            'result' => 'OK',
            'order' => $order,
        ], 200);
    }

    public function updateOrder(Request $request, string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                "code" => 100,
                "description" => "Заказ не найден"
            ], 400);
        }

        $order->update($request->all());
        if ($request->items) {
            $order->items()->delete();
            foreach ($request->items as $item) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $item['id'];
                $orderItem->qty = $item['quantity'];
                $orderItem->price = $item['price'];
                $orderItem->sku = \App\Models\Product::where('id', $item['id'])->first()->sku;
                $orderItem->save();
            }
        }
        return response()->json([
            'result' => 'OK',
            'orderId' => $order->id,
        ], 200);
    }

    public function deleteOrder(string $id)
    {
        $eatsId = request()->input('eatsId');
        $order = Order::where('eatsId', $eatsId)->where('id', $id)->first();

        if (!$order) {
            return response()->json([
                "code" => 100,
                "description" => "Заказ не найден"
            ], 400);
        }
        $order->delete();

        return response()->json([
            'result' => 'OK',
            'orderId' => $id,
        ], 200);
    }

    public function getOrderStatus(string $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json([
                "code" => 100,
                "description" => "Заказ не найден"
            ], 400);
        }


        return response()->json([
            'status' => match ($order->status) {
                Order::STATUS_NEW => 'NEW',
                Order::STATUS_PAID => 'PAID',
                Order::STATUS_CANCELLED => 'CANCELLED',
            },
            'comment' => '',
            'updatedAt' => $order->updated_at,
        ], 200);
    }
}
