@php
    $summary = $summary ?? null;
@endphp

<div class="space-y-3">
    @if (!$summary)
        <p class="text-sm text-gray-600">Сводка недоступна: клиент не найден.</p>
    @else
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-lg border p-3">
                <div class="text-xs text-gray-500">ID клиента</div>
                <div class="text-sm font-medium">{{ $summary['client_id'] }}</div>
            </div>
            <div class="rounded-lg border p-3">
                <div class="text-xs text-gray-500">Количество заказов</div>
                <div class="text-sm font-medium">{{ $summary['orders_count'] }}</div>
            </div>
            <div class="rounded-lg border p-3">
                <div class="text-xs text-gray-500">Оплаченных заказов</div>
                <div class="text-sm font-medium">{{ $summary['paid_orders_count'] }}</div>
            </div>
            <div class="rounded-lg border p-3">
                <div class="text-xs text-gray-500">Количество адресов</div>
                <div class="text-sm font-medium">{{ $summary['addresses_count'] }}</div>
            </div>
            <div class="rounded-lg border p-3">
                <div class="text-xs text-gray-500">Сумма заказов</div>
                <div class="text-sm font-medium">{{ number_format((int) $summary['orders_total'], 0, ',', ' ') }} ₽</div>
            </div>
            <div class="rounded-lg border p-3">
                <div class="text-xs text-gray-500">Средний чек</div>
                <div class="text-sm font-medium">{{ number_format((int) $summary['average_order_total'], 0, ',', ' ') }} ₽</div>
            </div>
        </div>

        <div class="rounded-lg border p-3">
            <div class="text-xs text-gray-500">Последний заказ</div>
            <div class="text-sm font-medium">
                {{ $summary['last_order_at'] ? \Illuminate\Support\Carbon::parse($summary['last_order_at'])->format('d.m.Y H:i') : 'Нет заказов' }}
            </div>
        </div>
    @endif
</div>

