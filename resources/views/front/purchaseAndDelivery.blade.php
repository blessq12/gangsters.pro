@extends('layouts.front')

@section('title', 'Оплата и доставка')
@section('desc', '')

@section('hero')
    <x-front.hero-banner
        title="Оплата и доставка"
        description="Правила и условия оплаты и доставки продукции"
        :overlay="true"
    ></x-front.hero-banner>
@endsection

@section('content')
    <div class="container py-5">
        <div class="row row-cols-1 row-cols-lg-2">
            <div class="col mb-4 mb-lg-0">
                <h4 class="mb-3" style="font-weight: 500;">Оплата и доставка</h4>
                <p class="fw-bold">
                    Мы принимаем и доставляем заказы с {{ \Carbon\Carbon::parse($company->currentDayShedule()->open_time)->format('H:i') }} до {{ \Carbon\Carbon::parse($company->currentDayShedule()->close_time)->format('H:i') }}
                </p>
                <p>
                В нашем магазине вы можете оплатить заказ следующими способами:
                <ul>
                    <li>Наличными при получении</li>
                    <li>
                        <span style="text-decoration: line-through; color: #6c757d; margin-right: 6px;">Банковской картой онлайн</span>
                        <span class="text-danger">(временно недоступен)</span>
                    </li>
                    <li>Банковской картой при получении</li>
                </ul>
                <p>Доставка осуществляется в пределах города и ближайших пригородов. Стоимость доставки зависит от расстояния и рассчитывается автоматически при оформлении заказа.</p>
                <p>Мы стараемся доставить ваш заказ максимально быстро, среднее время доставки составляет от 30 до 60 минут, в зависимости от загруженности дорог и удаленности района.</p>
                </p>
            </div>
            <div class="col">
                <h4 class="mb-3" style="font-weight: 500;">Карта зоны доставки</h4>
                <div style="overflow: hidden; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);" class="rounded">
                    <iframe src="https://yandex.ru/map-widget/v1/?lang=ru_RU&amp;scroll=true&amp;source=constructor-api&amp;um=constructor%3A397d1a49dae9746843a7b0c4a118de57a5842e785237a27e52e721233acafa41" frameborder="0" allowfullscreen="true" width="100%" height="400px" style="display: block;"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection