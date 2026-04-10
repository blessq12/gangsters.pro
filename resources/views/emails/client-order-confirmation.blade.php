@php
    $fmtRub = static fn (mixed $rub): string => \App\Support\Money::formatRublesRuAdaptive((float) $rub);

    $deliveryBlock = $order['delivery'] ?? [];
    $deliveryMethod = is_array($deliveryBlock) ? ($deliveryBlock['method'] ?? '') : '';
    $deliveryLabel = match ($deliveryMethod) {
        'courier' => 'Курьер',
        'pickup' => 'Самовывоз',
        default => $deliveryMethod !== '' ? $deliveryMethod : '—',
    };

    $paymentBlock = $order['payment'] ?? [];
    $payMethod = is_array($paymentBlock) ? ($paymentBlock['method'] ?? '') : '';
    $paymentLabel = match ($payMethod) {
        'cash' => 'Наличные',
        'card' => 'Банковская карта',
        'transfer' => 'Перевод',
        default => $payMethod !== '' ? $payMethod : '—',
    };

    $payStatus = is_array($paymentBlock) ? ($paymentBlock['status'] ?? '') : '';
    $paymentStatusLabel = match ($payStatus) {
        'unpaid' => 'Не оплачен',
        'processing' => 'Платёж в обработке',
        'paid' => 'Оплачен',
        default => $payStatus !== '' ? $payStatus : '—',
    };

    $statusRaw = $order['status'] ?? '';
    $statusLabel = match ($statusRaw) {
        'new' => 'Принят',
        'preparing' => 'Готовится',
        'in_transit' => 'В пути',
        'delivered' => 'Доставлен',
        default => $statusRaw !== '' ? $statusRaw : '—',
    };

    $customer = $order['customer'] ?? [];
    $addr = $customer['address'] ?? null;
    $addressLine = '—';
    if (is_array($addr)) {
        $bits = array_filter([
            $addr['street'] ?? null,
            isset($addr['house']) ? 'д. '.$addr['house'] : null,
            isset($addr['entrance']) ? 'подъезд '.$addr['entrance'] : null,
            isset($addr['apartment']) ? 'кв./офис '.$addr['apartment'] : null,
        ]);
        $addressLine = $bits !== [] ? implode(', ', $bits) : '—';
    }

    $deliveryAddr = is_array($deliveryBlock) ? ($deliveryBlock['address'] ?? null) : null;
    $deliveryAddressLine = '—';
    if (is_array($deliveryAddr)) {
        $bits = array_filter([
            $deliveryAddr['street'] ?? null,
            isset($deliveryAddr['house']) ? 'д. '.$deliveryAddr['house'] : null,
            isset($deliveryAddr['entrance']) ? 'подъезд '.$deliveryAddr['entrance'] : null,
            isset($deliveryAddr['apartment']) ? 'кв./офис '.$deliveryAddr['apartment'] : null,
        ]);
        $deliveryAddressLine = $bits !== [] ? implode(', ', $bits) : '—';
    }

    $items = $order['items'] ?? [];
@endphp
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Заказ</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.55; color: #1f1f23;">
    <p>Спасибо за заказ в Gangsters!</p>
    <p>Номер заказа: <strong>{{ $order['id'] ?? '—' }}</strong><br>
        Статус: <strong>{{ $statusLabel }}</strong></p>

    <p style="margin-top: 1.25em;"><strong>Состав</strong></p>
    <table cellpadding="0" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 480px; font-size: 14px;">
        @foreach ($items as $row)
            @php
                $p = $row['product'] ?? [];
                $name = $p['name'] ?? 'Товар';
                $qty = (int) ($row['quantity'] ?? 0);
                $rowTotalRub = (float) ($row['row_total'] ?? 0);
            @endphp
            <tr>
                <td style="padding: 6px 8px 6px 0; vertical-align: top; border-bottom: 1px solid #e2e8f0;">
                    {{ $name }}<br>
                    <span style="color: #64748b; font-size: 12px;">{{ $qty }}&nbsp;шт. × {{ $fmtRub($row['unit_price'] ?? 0) }}&nbsp;₽</span>
                </td>
                <td style="padding: 6px 0; vertical-align: top; text-align: right; white-space: nowrap; border-bottom: 1px solid #e2e8f0;">
                    {{ $fmtRub($rowTotalRub) }}&nbsp;₽
                </td>
            </tr>
        @endforeach
    </table>

    <p style="margin-top: 1em; font-size: 14px;">
        Позиции: <strong>{{ $fmtRub($order['subtotal'] ?? 0) }}&nbsp;₽</strong><br>
        @if ((float) ($order['discount_total'] ?? 0) > 0)
            Скидка: <strong>−{{ $fmtRub($order['discount_total'] ?? 0) }}&nbsp;₽</strong><br>
        @endif
        <span style="font-size: 16px;">Итого: <strong>{{ $fmtRub($order['total'] ?? 0) }}&nbsp;₽</strong></span>
    </p>

    <p style="margin-top: 1.25em;"><strong>Контакты</strong></p>
    <p style="font-size: 14px; margin: 0;">
        {{ $customer['name'] ?? '—' }}<br>
        Телефон: {{ $customer['phone'] ?? '—' }}<br>
        @if (!empty($customer['email']))
            Email: {{ $customer['email'] }}<br>
        @endif
        Адрес в профиле: {{ $addressLine }}
    </p>

    <p style="margin-top: 1.25em;"><strong>Доставка и оплата</strong></p>
    <p style="font-size: 14px; margin: 0;">
        Способ: {{ $deliveryLabel }}<br>
        @if ($deliveryMethod === 'courier')
            Адрес доставки: {{ $deliveryAddressLine }}<br>
        @endif
        @if (is_array($deliveryBlock) && !empty($deliveryBlock['comment']))
            Комментарий: {{ $deliveryBlock['comment'] }}<br>
        @endif
        Оплата: {{ $paymentLabel }} ({{ $paymentStatusLabel }})
    </p>

    <p style="margin-top: 1.5em; font-size: 12px; color: #64748b;">
        Если что-то не так с заказом — ответь на это письмо или напиши нам из приложения.
    </p>
</body>
</html>
