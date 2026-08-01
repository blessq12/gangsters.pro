<?php

namespace App\Infrastructure\Integration\Frontpad;

use App\Domain\Order\Event\OrderCreated;
use InvalidArgumentException;

/**
 * ACL: OrderCreated → Frontpad new_order form params.
 */
final class FrontpadOrderMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toRequest(OrderCreated $event): array
    {
        $client = $event->client();
        $delivery = $event->delivery();
        $payment = $event->payment();

        $request = [
            'secret' => (string) config('frontpad.secret', ''),
            'phone' => $this->normalizePhone(isset($client['phone']) ? (string) $client['phone'] : null),
            'name' => $this->truncate((string) ($client['name'] ?? ''), 50),
            'mail' => $this->truncate((string) ($client['email'] ?? ''), 50),
            'descr' => $this->buildDescription($event),
            'pay' => $this->resolvePayCode((string) ($payment['method'] ?? 'cash')),
            'person' => $this->resolvePersonCount(),
            'score' => 0,
            'sale' => 0,
            'sale_amount' => 0,
            'card' => '',
            'certificate' => '',
        ];

        $point = config('frontpad.point');
        if (is_string($point) && $point !== '') {
            $request['point'] = $point;
        }

        $channel = $this->resolveChannel($event);
        if ($channel !== '') {
            $request['channel'] = $channel;
        }

        $affiliate = config('frontpad.affiliate');
        if (is_string($affiliate) && $affiliate !== '') {
            $request['affiliate'] = $affiliate;
        }

        $tags = config('frontpad.tags');
        if (is_array($tags) && $tags !== []) {
            $request['tags'] = array_values($tags);
        }

        $scheduledAt = $delivery['scheduled_at'] ?? null;
        if (is_string($scheduledAt) && $scheduledAt !== '') {
            $request['datetime'] = $this->formatScheduledAt($scheduledAt);
        }

        $this->appendAddress($request, $delivery);
        $this->appendProducts($request, $event);
        $this->appendWebhook($request);

        return $request;
    }

    /**
     * @param  array<string, mixed>  $request
     * @param  array<string, mixed>  $delivery
     */
    private function appendAddress(array &$request, array $delivery): void
    {
        if (($delivery['method'] ?? '') === 'pickup') {
            return;
        }

        $address = $delivery['address'] ?? null;
        if (! is_array($address)) {
            return;
        }

        $request['street'] = $this->truncate((string) ($address['street'] ?? ''), 50);
        $request['home'] = $this->truncate((string) ($address['house'] ?? ''), 50);

        $entrance = $address['entrance'] ?? null;
        if (is_string($entrance) && $entrance !== '') {
            $request['pod'] = $this->truncate($entrance, 2);
        }

        $apartment = $address['apartment'] ?? null;
        if (is_string($apartment) && $apartment !== '') {
            $request['apart'] = $this->truncate($apartment, 50);
        }
    }

    /**
     * @param  array<string, mixed>  $request
     */
    private function appendProducts(array &$request, OrderCreated $event): void
    {
        $products = [];
        $quantities = [];
        $modifiers = [];
        $prices = [];
        $index = 0;

        foreach ($event->cart()['lines'] ?? [] as $line) {
            if (! is_array($line)) {
                continue;
            }

            $article = $this->resolveProductArticle($line);
            if ($article === null || $article === '') {
                throw new InvalidArgumentException(sprintf(
                    'Product #%d has no catalog SKU for Frontpad export.',
                    (int) ($line['product_id'] ?? 0),
                ));
            }

            $products[$index] = $this->normalizeProductArticle($article);
            $quantities[$index] = (int) ($line['quantity'] ?? 0);

            $payload = is_array($line['payload'] ?? null) ? $line['payload'] : null;
            $linePrice = null;

            if (is_array($payload)) {
                if (array_key_exists('parent_index', $payload)) {
                    $modifiers[$index] = (int) $payload['parent_index'];
                } elseif (array_key_exists('parent_id', $payload)) {
                    $modifiers[$index] = (int) $payload['parent_id'];
                }

                if (array_key_exists('custom_price', $payload)) {
                    $linePrice = $payload['custom_price'];
                }
            }

            $unitPrice = (int) ($line['unit_price_rubles'] ?? 0);
            if ($linePrice === null && $unitPrice === 0) {
                $linePrice = 0;
            }

            if ($linePrice !== null) {
                $prices[$index] = $linePrice;
            }

            $index++;
        }

        if ($index === 0) {
            throw new InvalidArgumentException('Order has no lines for Frontpad export.');
        }

        $request['product'] = $products;
        $request['product_kol'] = $quantities;

        if ($modifiers !== []) {
            $request['product_mod'] = $modifiers;
        }

        if ($prices !== []) {
            $request['product_price'] = $prices;
        }
    }

    /**
     * @param  array<string, mixed>  $request
     */
    private function appendWebhook(array &$request): void
    {
        $hookUrl = config('frontpad.hook_url');
        if (! is_string($hookUrl) || $hookUrl === '') {
            $hookUrl = rtrim((string) config('app.url'), '/').'/api/orders/update';
        }

        $request['hook_url'] = $hookUrl;

        $hookStatus = config('frontpad.hook_status');
        if (is_array($hookStatus) && $hookStatus !== []) {
            $request['hook_status'] = array_values($hookStatus);
        }
    }

    private function buildDescription(OrderCreated $event): string
    {
        $parts = [
            sprintf('Заказ #%d', $event->orderId()),
        ];

        $comment = $event->delivery()['comment'] ?? null;
        if (is_string($comment) && trim($comment) !== '') {
            $parts[] = trim($comment);
        }

        return $this->truncate(implode('. ', $parts), 100);
    }

    private function resolvePayCode(string $method): string
    {
        $map = config('frontpad.pay', []);
        if (! is_array($map)) {
            $map = [];
        }

        $defaults = [
            'cash' => '1',
            'card_courier' => '2',
            'card_online' => '2',
        ];

        $code = $map[$method] ?? $defaults[$method] ?? '1';

        return (string) $code;
    }

    private function resolveChannel(OrderCreated $event): string
    {
        $channel = config('frontpad.channel');
        if (is_string($channel) && $channel !== '') {
            return $channel;
        }

        return $event->source() === 'aggregator' ? 'aggregator' : '';
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function resolveProductArticle(array $line): ?string
    {
        $sku = $line['sku'] ?? null;
        if (! is_string($sku)) {
            return null;
        }

        $sku = trim($sku);

        return $sku !== '' ? $sku : null;
    }

    private function resolvePersonCount(): int
    {
        $person = config('frontpad.person');

        return min(max((int) ($person ?? 1), 1), 99);
    }

    private function normalizeProductArticle(string $article): int|string
    {
        if (ctype_digit($article)) {
            return (int) $article;
        }

        return $article;
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        return $this->truncate($digits, 50);
    }

    private function formatScheduledAt(string $scheduledAt): string
    {
        $timestamp = strtotime($scheduledAt);

        if ($timestamp === false) {
            return $scheduledAt;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function truncate(string $value, int $maxLength): string
    {
        if (mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return mb_substr($value, 0, $maxLength);
    }
}
