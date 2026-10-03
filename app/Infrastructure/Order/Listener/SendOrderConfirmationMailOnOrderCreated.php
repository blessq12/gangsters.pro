<?php

namespace App\Infrastructure\Order\Listener;

use App\Domain\Order\Event\OrderCreated;
use App\Mail\OrderCreatedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Order: отбивка клиенту после создания заказа (если есть email).
 */
final class SendOrderConfirmationMailOnOrderCreated
{
    public function handle(OrderCreated $event): void
    {
        $client = $event->client();
        $email = trim((string) ($client['email'] ?? ''));
        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->send(new OrderCreatedMail(
                orderId: $event->orderId(),
                clientName: trim((string) ($client['name'] ?? '')),
                lineSummaries: $this->lineSummaries($event),
                totalRubles: $this->totalRubles($event),
                deliveryMethod: $this->deliveryLabel($event),
                paymentMethod: $this->paymentLabel($event),
            ));
        } catch (Throwable $exception) {
            Log::error('Не удалось отправить письмо о заказе', [
                'order_id' => $event->orderId(),
                'email' => $email,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @return list<string>
     */
    private function lineSummaries(OrderCreated $event): array
    {
        $summaries = [];

        foreach ($event->cart()['lines'] ?? [] as $line) {
            if (! is_array($line)) {
                continue;
            }

            $name = trim((string) ($line['product_name'] ?? $line['name'] ?? 'Позиция'));
            $qty = max(1, (int) ($line['quantity'] ?? 1));
            $lineTotal = (int) ($line['line_total_rubles'] ?? 0);
            $summaries[] = "{$name} × {$qty} — {$lineTotal} ₽";
        }

        return $summaries;
    }

    private function totalRubles(OrderCreated $event): int
    {
        $total = 0;

        foreach ($event->cart()['lines'] ?? [] as $line) {
            if (! is_array($line)) {
                continue;
            }

            $kind = is_array($line['payload'] ?? null)
                ? (string) (($line['payload']['kind'] ?? '') ?: 'user')
                : 'user';

            if (in_array($kind, ['gift', 'complement'], true)) {
                continue;
            }

            $total += (int) ($line['line_total_rubles'] ?? 0);
        }

        $deliveryFee = (int) ($event->delivery()['delivery_fee_rubles'] ?? 0);

        return $total + max(0, $deliveryFee);
    }

    private function deliveryLabel(OrderCreated $event): string
    {
        $method = (string) ($event->delivery()['method'] ?? '');

        return match ($method) {
            'courier' => 'Доставка курьером',
            'pickup' => 'Самовывоз',
            default => $method !== '' ? $method : 'не указано',
        };
    }

    private function paymentLabel(OrderCreated $event): string
    {
        $method = (string) ($event->payment()['method'] ?? '');

        return match ($method) {
            'cash' => 'Наличными',
            'card_courier' => 'Картой курьеру',
            'card_online' => 'Картой онлайн',
            'card' => 'Картой',
            default => $method !== '' ? $method : 'не указано',
        };
    }
}
