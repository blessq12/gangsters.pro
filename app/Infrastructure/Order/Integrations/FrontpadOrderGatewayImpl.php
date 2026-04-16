<?php

namespace App\Infrastructure\Order\Integrations;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Integrations\FrontpadOrderGateway;
use App\Domain\Order\Enums\PaymentMethod;
use GuzzleHttp\ClientInterface;

final class FrontpadOrderGatewayImpl implements FrontpadOrderGateway
{
    public function __construct(
        private readonly ClientInterface $http,
        private readonly string $apiUrl,
        private readonly string $apiSecret,
        private readonly string $hookUrl,
        private readonly bool $failSilently = true,
    ) {
    }

    public function pushOrder(Order $order): void
    {
        $customer = $order->getCustomer();
        $delivery = $order->getDeliveryInfo();
        $payment = $order->getPaymentInfo();

        $paymentMapping = [
            PaymentMethod::Cash->value => 1,
            PaymentMethod::Card->value => 2,
        ];

        $payload = [
            'secret' => $this->apiSecret,
            'product' => [],
            'product_kol' => [],
            'product_mod' => [],
            'product_price' => [],
            'score' => 0,
            'sale' => 0,
            'sale_amount' => 0,
            'card' => '',
            'street' => $delivery?->address['street'] ?? $customer->address['street'] ?? '',
            'home' => $delivery?->address['house'] ?? $customer->address['house'] ?? '',
            'pod' => $delivery?->address['entrance'] ?? $customer->address['entrance'] ?? '',
            'et' => '',
            'apart' => $delivery?->address['apartment'] ?? $customer->address['apartment'] ?? '',
            'phone' => $customer->phone ?? '',
            'mail' => $customer->email ?? '',
            'descr' => $delivery?->comment
                ? (mb_strlen($delivery->comment) > 100
                    ? mb_substr($delivery->comment, 0, 100)
                    : $delivery->comment)
                : '',
            'name' => $customer->name ?? '',
            'pay' => $payment ? ($paymentMapping[$payment->method] ?? 1) : 1,
            'certificate' => '',
            'person' => 1,
            'tags' => [],
            'hook_status' => [1, 10, 11],
            'hook_url' => $this->hookUrl,
            'channel' => '',
            'datetime' => '',
            'affiliate' => '',
            'point' => '',
        ];

        foreach ($order->getItems() as $idx => $item) {
            $product = $item->getProduct();
            $payload['product'][$idx] = (int) ($product->sku ?? $product->name);
            $payload['product_kol'][$idx] = (int) $item->getQuantity();
        }

        try {
            $response = $this->http->request('POST', $this->apiUrl . '?new_order', [
                'form_params' => $payload,
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if (!is_array($body) || ($body['result'] ?? null) !== 'success') {
                if (! $this->failSilently) {
                    throw new \RuntimeException('Frontpad returned unsuccessful response.');
                }
            }
        } catch (\Throwable $e) {
            if (! $this->failSilently) {
                throw $e;
            }
        }
    }
}

