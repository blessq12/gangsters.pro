<?php

namespace App\Application\OrderAccountingExport\Mapper;

use App\Domain\Order\Event\OrderCreated;
use App\Domain\OrderAccountingExport\Exception\UnknownAccountingProductException;
use App\Domain\OrderAccountingExport\Repository\AccountingProductBindingRepository;
use App\Shared\Enum\DeliveryMethod;
use App\Shared\Enum\PaymentMethod;

/**
 * ACL: OrderCreated → form-параметры Frontpad new_order.
 */
final class FrontpadOrderMapper
{
    private const SYSTEM_CODE = 'frontpad';

    public function __construct(
        private readonly AccountingProductBindingRepository $productBindings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toRequest(OrderCreated $event): array
    {
        $request = [
            'secret' => (string) config('order-accounting-export.systems.frontpad.secret', ''),
            'phone' => $this->normalizePhone($event->client()->phone()),
            'name' => $this->truncate((string) ($event->client()->name() ?? ''), 50),
            'mail' => $this->truncate((string) ($event->client()->email() ?? ''), 50),
            'descr' => $this->buildDescription($event),
        ];

        $payCode = $this->resolvePayCode($event->payment()->method());
        if ($payCode !== null && $payCode !== '') {
            $request['pay'] = $payCode;
        }

        $point = config('order-accounting-export.systems.frontpad.point');
        if (is_string($point) && $point !== '') {
            $request['point'] = $point;
        }

        $channel = config('order-accounting-export.systems.frontpad.channel');
        if (is_string($channel) && $channel !== '') {
            $request['channel'] = $channel;
        }

        $scheduledAt = $event->delivery()->scheduledAt();
        if (is_string($scheduledAt) && $scheduledAt !== '') {
            $request['datetime'] = $this->formatScheduledAt($scheduledAt);
        }

        $this->appendAddress($request, $event);
        $this->appendProducts($request, $event);

        return $request;
    }

    /**
     * @param  array<string, mixed>  $request
     */
    private function appendAddress(array &$request, OrderCreated $event): void
    {
        if ($event->delivery()->method() === DeliveryMethod::Pickup) {
            return;
        }

        $address = $event->delivery()->address();
        if ($address === null) {
            return;
        }

        $request['street'] = $this->truncate($address->street(), 50);
        $request['home'] = $this->truncate($address->house(), 50);

        if ($address->entrance() !== null && $address->entrance() !== '') {
            $request['pod'] = $this->truncate($address->entrance(), 2);
        }

        if ($address->apartment() !== null && $address->apartment() !== '') {
            $request['apart'] = $this->truncate($address->apartment(), 50);
        }
    }

    /**
     * @param  array<string, mixed>  $request
     */
    private function appendProducts(array &$request, OrderCreated $event): void
    {
        $index = 0;

        foreach ($event->cart()->lines() as $line) {
            if ($line->isPromotionBenefitLine()) {
                continue;
            }

            $article = $this->productBindings->resolveExternalProductId(self::SYSTEM_CODE, $line->productId());
            if ($article === null || $article === '') {
                throw new UnknownAccountingProductException(self::SYSTEM_CODE, $line->productId());
            }

            $request[sprintf('product[%d]', $index)] = $article;
            $request[sprintf('product_kol[%d]', $index)] = (string) $line->quantity();
            $request[sprintf('product_price[%d]', $index)] = (string) $line->unitPrice()->amountRubles();

            $index++;
        }

        if ($index === 0) {
            throw new \InvalidArgumentException('Заказ не содержит позиций для экспорта в Frontpad.');
        }
    }

    private function buildDescription(OrderCreated $event): string
    {
        $parts = [
            sprintf('Заказ #%d', $event->orderId()->value()),
        ];

        $comment = $event->delivery()->comment();
        if (is_string($comment) && trim($comment) !== '') {
            $parts[] = trim($comment);
        }

        return $this->truncate(implode('. ', $parts), 100);
    }

    private function resolvePayCode(PaymentMethod $method): ?string
    {
        $map = config('order-accounting-export.systems.frontpad.pay', []);
        if (! is_array($map)) {
            return null;
        }

        $code = $map[$method->value] ?? null;

        return is_string($code) ? $code : null;
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
