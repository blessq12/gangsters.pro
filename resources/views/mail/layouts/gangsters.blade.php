@php
    $logoUrl = url('/favicon/apple-touch-icon.png');
    $brandName = trim((string) ($brandName ?? ''));
    if ($brandName === '') {
        $brandName = \App\Mail\Support\MailBrandName::resolve();
    }
@endphp
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark light">
    <title>{{ $title ?? $brandName }}</title>
</head>
<body style="margin:0;padding:0;background-color:#191919;-webkit-text-size-adjust:100%;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#191919;margin:0;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;background-color:#ececec;border-collapse:collapse;">
                    <tr>
                        <td style="height:4px;line-height:4px;font-size:0;background-color:#c62424;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding:20px 24px 12px 24px;background-color:#191919;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="vertical-align:middle;padding-right:12px;">
                                        <img src="{{ $logoUrl }}" width="40" height="40" alt="{{ $brandName }}" style="display:block;border:0;border-radius:8px;">
                                    </td>
                                    <td style="vertical-align:middle;font-family:Arial,Helvetica,sans-serif;font-size:18px;font-weight:700;letter-spacing:0.04em;color:#ececec;text-transform:uppercase;">
                                        {{ $brandName }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 24px 8px 24px;font-family:Arial,Helvetica,sans-serif;font-size:16px;line-height:1.55;color:#191919;">
                            {{ $slot }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 24px 28px 24px;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:1.5;color:#666666;">
                            {{ $footer ?? 'Если письмо пришло по ошибке — просто проигнорируйте его.' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 24px;background-color:#191919;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.4;color:#a3a3a3;text-align:center;">
                            @if ($brandName !== '')
                                © {{ date('Y') }} {{ $brandName }}
                            @else
                                © {{ date('Y') }}
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
