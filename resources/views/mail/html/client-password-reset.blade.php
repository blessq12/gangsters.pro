@component('mail.layouts.gangsters', [
    'title' => 'Новый пароль',
    'footer' => 'Если вы не запрашивали сброс — смените пароль после входа или напишите в поддержку.',
])
    <p style="margin:0 0 16px 0;">
        Здравствуйте{{ $clientName !== '' ? ', '.$clientName : '' }}!
    </p>
    <p style="margin:0 0 16px 0;">
        Мы сгенерировали новый пароль для входа в личный кабинет:
    </p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 16px 0;">
        <tr>
            <td style="padding:14px 16px;background-color:#191919;color:#ececec;font-family:Consolas,'Courier New',monospace;font-size:18px;font-weight:700;letter-spacing:0.06em;text-align:center;border-radius:4px;">
                {{ $plainPassword }}
            </td>
        </tr>
    </table>
    <p style="margin:0;">
        Войдите с этим паролем на сайте.
    </p>
@endcomponent
