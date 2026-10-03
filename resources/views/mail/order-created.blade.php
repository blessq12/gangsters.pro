Здравствуйте{{ $clientName !== '' ? ', '.$clientName : '' }}!

Ваш заказ №{{ $orderId }} принят.

Состав:
@foreach ($lineSummaries as $line)
- {{ $line }}
@endforeach

Итого: {{ $totalRubles }} ₽
Получение: {{ $deliveryMethod }}
Оплата: {{ $paymentMethod }}

Если есть вопросы — напишите или позвоните нам.
