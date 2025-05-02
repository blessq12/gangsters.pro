@extends('layouts.front')

@section('title', 'Оплата и доставка')
@section('desc', '')

@section('hero')
    <x-front.hero-banner title="Оплата и доставка" description="Правила и условия оплаты и доставки продукции"
        :overlay="true" banner='/images/hero-bg.webp'></x-front.hero-banner>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-6">
                <h4 class="text-2xl font-medium text-gray-900">Оплата и доставка</h4>
                <p class="text-lg font-semibold text-gray-900">
                    Мы принимаем и доставляем заказы с
                    {{ \Carbon\Carbon::parse($company->currentDayShedule()->open_time)->format('H:i') }} до
                    {{ \Carbon\Carbon::parse($company->currentDayShedule()->close_time)->format('H:i') }}
                </p>
                <div class="space-y-4">
                    <p class="text-gray-700">В нашем магазине вы можете оплатить заказ следующими способами:</p>
                    <ul class="space-y-3">
                        <li class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            <span>Наличными при получении</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="line-through text-gray-400">Банковской картой онлайн</span>
                            <span class="text-red-500 text-sm">(временно недоступен)</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            <span>Банковской картой при получении</span>
                        </li>
                    </ul>
                    <div class="space-y-4 text-gray-700">
                        <p>Доставка осуществляется в пределах города и ближайших пригородов. Стоимость доставки зависит от
                            расстояния.</p>
                        <p>Мы стараемся доставить ваш заказ максимально быстро, среднее время доставки составляет от 90 до
                            120 минут, в зависимости от загруженности дорог и удаленности района.</p>
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                <h4 class="text-2xl font-medium text-gray-900">Карта зоны доставки</h4>
                <div class="overflow-hidden rounded-lg shadow-lg bg-white">
                    <iframe
                        src="https://yandex.ru/map-widget/v1/?lang=ru_RU&amp;scroll=true&amp;source=constructor-api&amp;um=constructor%3A397d1a49dae9746843a7b0c4a118de57a5842e785237a27e52e721233acafa41"
                        class="w-full h-[400px] border-0" allowfullscreen="true">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
@endsection
