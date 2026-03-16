<?php

namespace Database\Seeders;

use App\Infrastructure\Order\Model\ORD_Order;
use App\Infrastructure\Order\Model\ORD_OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = [
            [
                'client_id' => 0,
                'status' => 'new',
                'customer_name' => 'Гость',
                'customer_phone' => '+7 (999) 111-22-33',
                'customer_email' => null,
                'items' => [
                    ['name' => 'Пепперони', 'sku' => 'PIZ-001', 'list' => 45000, 'final' => 45000, 'qty' => 1],
                    ['name' => 'Кола 0.5', 'sku' => 'DRK-001', 'list' => 8000, 'final' => 8000, 'qty' => 2],
                ],
            ],
            [
                'client_id' => 0,
                'status' => 'preparing',
                'customer_name' => 'Иван Гостев',
                'customer_phone' => '+7 (999) 222-33-44',
                'customer_email' => 'guest@example.com',
                'items' => [
                    ['name' => 'Маргарита', 'sku' => 'PIZ-002', 'list' => 39900, 'final' => 35900, 'qty' => 2],
                ],
            ],
            [
                'client_id' => 1,
                'status' => 'in_transit',
                'customer_name' => 'Мария Клиентова',
                'customer_phone' => '+7 (999) 333-44-55',
                'customer_email' => 'maria@example.com',
                'items' => [
                    ['name' => 'Четыре сыра', 'sku' => 'PIZ-003', 'list' => 52000, 'final' => 52000, 'qty' => 1],
                    ['name' => 'Чесночный соус', 'sku' => 'SAU-001', 'list' => 5000, 'final' => 5000, 'qty' => 1],
                ],
            ],
            [
                'client_id' => 1,
                'status' => 'delivered',
                'customer_name' => 'Мария Клиентова',
                'customer_phone' => '+7 (999) 333-44-55',
                'customer_email' => 'maria@example.com',
                'items' => [
                    ['name' => 'Комбо обед', 'sku' => 'COM-001', 'list' => 29900, 'final' => 24900, 'qty' => 1],
                ],
            ],
            [
                'client_id' => 0,
                'status' => 'new',
                'customer_name' => 'Отмена Гость',
                'customer_phone' => '+7 (999) 444-55-66',
                'customer_email' => null,
                'items' => [
                    ['name' => 'Тестовая пицца', 'sku' => 'PIZ-TEST', 'list' => 10000, 'final' => 10000, 'qty' => 1],
                ],
            ],
        ];

        foreach ($orders as $data) {
            $itemsData = $data['items'];
            unset($data['items']);

            $subtotal = 0;
            $discountTotal = 0;
            foreach ($itemsData as $row) {
                $subtotal += $row['final'] * $row['qty'];
                $discountTotal += ($row['list'] - $row['final']) * $row['qty'];
            }
            $total = $subtotal - $discountTotal;

            $order = ORD_Order::create([
                'id' => (string) Str::uuid(),
                'client_id' => $data['client_id'],
                'status' => $data['status'],
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'total' => $total,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'],
                'customer_address' => null,
                'delivery_method' => 'courier',
                'delivery_address' => ['street' => 'Тестовая', 'house' => '1'],
                'delivery_comment' => null,
                'payment_method' => 'cash',
                'payment_status' => null,
            ]);

            foreach ($itemsData as $row) {
                $rowSubtotal = $row['final'] * $row['qty'];
                $rowDiscount = ($row['list'] - $row['final']) * $row['qty'];
                $rowTotal = $rowSubtotal - $rowDiscount;

                ORD_OrderItem::create([
                    'order_id' => $order->id,
                    'product_original_id' => null,
                    'product_name' => $row['name'],
                    'product_sku' => $row['sku'],
                    'product_list_price' => $row['list'],
                    'product_final_price' => $row['final'],
                    'product_attributes' => [],
                    'product_media' => [],
                    'quantity' => $row['qty'],
                    'unit_price' => $row['final'],
                    'row_subtotal' => $rowSubtotal,
                    'row_discount' => $rowDiscount,
                    'row_total' => $rowTotal,
                ]);
            }
        }
    }
}
