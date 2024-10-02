<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Our Platform</title>
    <link rel="stylesheet" href="">
    @include('inc.cdn')
</head>
<style>
    :root {
        --color-main: #1f2b43;
    }
    h2{
        color: var(--color-main);
        text-transform: uppercase;
        font-weight: 700;
    }
    .rounded {
        border-radius: 22px !important; 
    }
    .banner {
        width: 100%;
        height: 300px;
        background-size: cover;
        background-position: center;
    }
    .card {
        border-color: var(--color-main);
        border-width: 2px;
        padding: 12px !important;
        border-radius: 22px !important; 
    }
    ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    ul li {
        margin-bottom: 6px;
        font-size: 14px !important;
    }
    ul li b {
        color: var(--color-main);
        font-weight: 700;
    }
    p {
        font-size: 14px !important;
    }
    .card-body {
        padding: 12px 8px !important;
    }
    
</style>
<body class="py-4">
    <header class="mb-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <div class="banner rounded" style="background-image: url(data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path('images/mailbanner.jpg'))) }});"></div>
                </div>
            </div>
        </div>
    </header>
    <content class="mb-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h1 class="text-uppercase fw-bold fs-2">Добро пожаловать в семью Гангстерc Суши!</h1>
                    <p class="mb-5">
                        Мы рады приветствовать вас в нашем сообществе ценителей истинного вкуса и дружбы. 
                        Здесь вы найдете не только самые вкусные суши, но и новые знакомства, увлекательные предложения и уникальные мероприятия. 
                        Готовьтесь к незабываемым приключениям вместе с нами и помните, что уважение и верность — основы нашего сервиса и отношений!
                    </p>
                    <div class="card mb-4 text-white position-relative overflow-hidden" style="background: #1f2b43; position: relative;">
                        <div class="position-absolute" style="
                            top: -50px; right: -50px;
                            transform: rotate(15deg);
                            width: 200px;
                            height: 200px;
                            opacity: 0.2;
                            background: url(data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path('/images/coin-icon.png'))) }});
                            background-size: contain;
                            background-position: center;
                            background-repeat: no-repeat;
                        " >
                        </div>
                        <div class="card-body">
                            <h4 class="fw-bold">Мы рады сообщить вам о нашей системе кешбека Gangsters Coin! </h4>
                            <p>
                                <p>
                                    Каждый раз, когда вы совершаете покупку, вы получаете кешбек в виде Gangsters Coin, который можно использовать для оплаты до 50% вашей следующей покупки. Это наш способ поблагодарить вас за вашу лояльность и сделать ваши покупки еще более выгодными!
                                </p>
                                <a href="{{ env('APP_URL') }}/loyalty" class="btn btn-outline-light">Подробнее</a>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-body">
                            <p class="fw-bold">Здесь вся информация о твоем аккаунте, которым ты можешь поделиться с друзьями, чтобы заработать больше Gangsters Coin</p>
                            <ul class="list-unstyled p-0 m-0">
                                <li><b>Номер телефона:</b> {{ $user->tel }}</li>
                                <li><b>Email:</b> {{ $user->email }}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <p class="fw-bold">Также оставим информацию о нас, чтобы не теряться в огромном мире</p>
                            <ul class="list-unstyled p-0 m-0">
                                <li><b>Телефон:</b> <a href="tel:{{ $company->phone }}">{{ $company->phone }}</a></li>
                                <li><b>Телефон:</b> <a href="tel:{{ $company->phone_additional }}">{{ $company->phone_additional }}</a></li>
                                <li><b>Email:</b> <a href="mailto:{{ $company->email_address }}">{{ $company->email_address }}</a></li>
                                <li><b>Адрес:</b> <a href="https://maps.google.com/?q={{ $company->city }}, {{ $company->street }}, {{ $company->house }}">{{ $company->city }}, {{ $company->street }}, {{ $company->house }}</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </content>
    <footer class="mt-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="footer">
                        <p class="fw-light mb-0">С любовью ❤️, команда <a href="{{ env('APP_URL') }}">Гангстерc Суши</a></p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <style>
        .footer{
            background: var(--color-main);
            color: white;
            padding: 24px 12px;
            border-radius: 16px;
        }
    </style>
</body>
</html>
