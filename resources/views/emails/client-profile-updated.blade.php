@php
    $yesNo = static fn ($v): string => $v ? 'да' : 'нет';

    $addresses = $client['addresses'] ?? [];
@endphp
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Профиль</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.55; color: #191919;">
    <p>Мы обновили данные твоего профиля в личном кабинете Gangsters. Ниже — как сейчас сохранено у нас.</p>

    <p style="margin-top: 1.25em;"><strong>Основное</strong></p>
    <p style="font-size: 14px; margin: 0;">
        Имя: {{ $client['name'] ?? '—' }}<br>
        Телефон: {{ $client['phone'] ?? '—' }}<br>
        Email: {{ $client['email'] ?? '—' }}<br>
        Дата рождения: {{ $client['birth_date'] ?? 'не указана' }}<br>
        Согласие на обработку данных: {{ $yesNo($client['consent_personal_data'] ?? false) }}<br>
        Рассылки: {{ $yesNo($client['consent_marketing'] ?? false) }}
    </p>

    @if ($addresses !== [])
        <p style="margin-top: 1.25em;"><strong>Сохранённые адреса</strong></p>
        @foreach ($addresses as $a)
            @php
                $typeLabel = ($a['type'] ?? '') === 'default' ? 'основной' : 'дополнительный';
                $title = $a['title'] ?? null;
                $bits = array_filter([
                    $a['street'] ?? null,
                    isset($a['house']) ? 'д. '.$a['house'] : null,
                    isset($a['entrance']) ? 'подъезд '.$a['entrance'] : null,
                    isset($a['apartment']) ? 'кв./офис '.$a['apartment'] : null,
                ]);
                $line = $bits !== [] ? implode(', ', $bits) : '—';
            @endphp
            <p style="font-size: 14px; margin: 0 0 10px 0; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
                {{ $typeLabel }}{{ $title ? ' — '.$title : '' }}<br>
                {{ $line }}
            </p>
        @endforeach
    @else
        <p style="margin-top: 1.25em; font-size: 14px; color: #64748b;">Адресов пока нет — можно добавить в приложении.</p>
    @endif

    <p style="margin-top: 1.5em; font-size: 12px; color: #64748b;">
        Если менял не ты — срочно смени пароль и напиши нам.
    </p>
</body>
</html>
