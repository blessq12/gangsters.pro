@extends('layouts.front')

@section('title', 'Контакты')
@section('desc', '')

@section('hero')
    <x-front.hero-banner title="Контакты" description="Контактная информация и режим работы" :overlay="true"
        banner='/images/hero-bg.webp' />
@endsection

@section('content')
    <div class="container mx-auto px-4 py-12">
        {{-- карта и отзывы --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
            <div class="lg:col-span-8">
                <div class="flex items-center gap-2 mb-4">
                    <i class="mdi mdi-map-marker text-2xl text-primary-600"></i>
                    <h4 class="text-xl font-medium">Мы на карте</h4>
                </div>
                <div class="rounded-xl overflow-hidden shadow-lg">
                    <iframe
                        src="https://yandex.ru/map-widget/v1/?ll=84.986330%2C56.513423&mode=search&oid=82888444717&ol=biz&z=16.65"
                        width="100%" height="400" frameborder="0" allowfullscreen="true" class="w-full"></iframe>
                </div>
            </div>
            <div class="lg:col-span-4">
                <div class="flex items-center gap-2 mb-4">
                    <i class="mdi mdi-star text-2xl text-primary-600"></i>
                    <h4 class="text-xl font-medium">Отзывы</h4>
                </div>
                <div class="rounded-xl overflow-hidden shadow-lg h-[400px]">
                    <iframe class="w-full h-full border border-gray-200"
                        src="https://yandex.ru/maps-reviews-widget/82888444717?comments">
                    </iframe>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {{-- График работы --}}
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center gap-2 mb-6">
                    <i class="mdi mdi-clock-outline text-2xl text-primary-600"></i>
                    <h4 class="text-xl font-medium">График работы</h4>
                </div>
                <ul class="space-y-3">
                    @foreach ($company->workShedules as $day)
                        <li class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                            <span class="text-gray-600">{{ $day->day }}</span>
                            <span class="font-medium">{{ \Carbon\Carbon::parse($day->open_time)->format('H:i') }}
                                - {{ \Carbon\Carbon::parse($day->close_time)->format('H:i') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Данные организации --}}
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center gap-2 mb-6">
                    <i class="mdi mdi-office-building text-2xl text-primary-600"></i>
                    <h4 class="text-xl font-medium">Данные организации</h4>
                </div>
                <ul class="space-y-3">
                    @foreach ($legals as $k => $v)
                        <li class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                            <span class="text-gray-600">{{ $k }}</span>
                            <span class="font-medium">{{ $v }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Контактная информация --}}
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center gap-2 mb-6">
                    <i class="mdi mdi-card-account-phone text-2xl text-primary-600"></i>
                    <h4 class="text-xl font-medium">Контактная информация</h4>
                </div>
                <ul class="space-y-4">
                    <li class="flex items-center gap-3">
                        <i class="mdi mdi-phone text-xl text-primary-600"></i>
                        <div class="flex flex-col">
                            <span class="text-sm text-gray-600">Телефон</span>
                            <a href="tel:{{ $company->phone }}"
                                class="font-medium hover:text-primary-600 transition-colors">
                                {{ $company->phone }}
                            </a>
                        </div>
                    </li>
                    <li class="flex items-center gap-3">
                        <i class="mdi mdi-map-marker text-xl text-primary-600"></i>
                        <div class="flex flex-col">
                            <span class="text-sm text-gray-600">Адрес</span>
                            <span class="font-medium">{{ $company->city }}, {{ $company->street }}, {{ $company->house }},
                                {{ $company->building }}</span>
                        </div>
                    </li>
                    <li class="flex items-center gap-3">
                        <i class="mdi mdi-email text-xl text-primary-600"></i>
                        <div class="flex flex-col">
                            <span class="text-sm text-gray-600">Электронная почта</span>
                            <a href="mailto:{{ $company->email_address }}"
                                class="font-medium hover:text-primary-600 transition-colors">
                                {{ $company->email_address }}
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection
