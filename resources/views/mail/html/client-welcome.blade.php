@component('mail.layouts.gangsters', [
    'title' => 'Добро пожаловать',
    'brandName' => $brandName,
    'footer' => 'Если письмо пришло по ошибке — просто проигнорируйте его.',
])
    <p style="margin:0 0 16px 0;">
        Здравствуйте{{ $clientName !== '' ? ', '.$clientName : '' }}!
    </p>
    <p style="margin:0 0 16px 0;">
        Спасибо за регистрацию{{ $brandName !== '' ? ' в '.$brandName : '' }}.
    </p>
    <p style="margin:0;">
        Личный кабинет доступен на сайте — входите по телефону или email и паролю, который указали при регистрации.
    </p>
@endcomponent
