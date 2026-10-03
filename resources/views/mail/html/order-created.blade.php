@component('mail.layouts.gangsters', [
    'title' => 'Заказ №'.$orderId,
    'brandName' => $brandName,
    'footer' => 'Если есть вопросы — напишите или позвоните нам.',
])
    <p style="margin:0 0 16px 0;">
        Здравствуйте{{ $clientName !== '' ? ', '.$clientName : '' }}!
    </p>
    <p style="margin:0 0 20px 0;">
        Ваш заказ <strong>№{{ $orderId }}</strong> принят.
    </p>

    <p style="margin:0 0 8px 0;font-size:13px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:#c62424;">
        Состав
    </p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px 0;border-collapse:collapse;">
        @foreach ($lineSummaries as $line)
            <tr>
                <td style="padding:10px 0;border-bottom:1px solid #d4d4d4;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.4;color:#191919;">
                    {{ $line }}
                </td>
            </tr>
        @endforeach
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;">
        <tr>
            <td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:15px;color:#191919;">
                <strong>Итого:</strong> {{ $totalRubles }} ₽
            </td>
        </tr>
        <tr>
            <td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:15px;color:#191919;">
                <strong>Получение:</strong> {{ $deliveryMethod }}
            </td>
        </tr>
        <tr>
            <td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:15px;color:#191919;">
                <strong>Оплата:</strong> {{ $paymentMethod }}
            </td>
        </tr>
    </table>
@endcomponent
