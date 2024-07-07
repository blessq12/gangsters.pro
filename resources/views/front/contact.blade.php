@extends('layouts.front')

@section('title', 'Контакты')
@section('desc', '')

@section('hero')
    <x-front.hero-banner
        title="Контакты"
        description="Контактная информация и режим работы"
        :overlay="true"
        banner='https://avatars.mds.yandex.net/get-altay/9833397/2a000001888d229695e50f5c88db8ab1417d/XXXL'
    />
@endsection

@section('content')
    <div class="container py-5">

        {{-- yandex map and testimonials --}}
        <div class="row mb-4">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div style="position: relative; overflow: hidden;" class="rounded">
                    <a href="https://yandex.ru/maps/org/gangster_s_sushi/82888444717/?utm_medium=mapframe&utm_source=maps" style="color: #EEE; font-size: 12px; position: absolute; top: 0;">
                        Gangster's sushi
                    </a>
                    <a href="https://yandex.ru/maps/67/tomsk/category/food_and_lunch_delivery/184108273/?utm_medium=mapframe&utm_source=maps" style="color: #EEE; font-size: 12px; position: absolute; top: 14px;">
                        Ресторан доставки в Томске
                    </a>
                    <iframe src="https://yandex.ru/map-widget/v1/?ll=84.986330%2C56.513423&mode=search&oid=82888444717&ol=biz&z=16.65" width="100%" height="400" frameborder="1" allowfullscreen="true" style="position: relative;"></iframe>
                </div>
            </div>
            <div class="col col-lg-4">
                <div style="width: 100%; height: 400px; overflow: hidden; position: relative;">
                    <iframe style="width: 100%; height: 100%; border: 1px solid #e6e6e6; border-radius: 8px; box-sizing: border-box;" src="https://yandex.ru/maps-reviews-widget/82888444717?comments"></iframe>
                    <a href="https://yandex.ru/maps/org/gangster_s_sushi/82888444717/" target="_blank" style="box-sizing: border-box; text-decoration: none; color: #b3b3b3; font-size: 10px; font-family: YS Text, sans-serif; position: absolute; bottom: 8px; width: 100%; text-align: center; left: 0; overflow: hidden; text-overflow: ellipsis; display: block; max-height: 14px; white-space: nowrap; padding: 0 16px;">
                        Gangster's sushi на карте Томска — Яндекс Карты
                    </a>
                </div>
            </div>
        </div>
        {{-- end yandex map and testimonials --}}

        <div class="row py-4 mb-4">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-3" style="font-weight: 500;">График работы</h4>
                        <ul class="list-unstyled p-0 m-0 work-shedule-list">
                        @foreach ($company->workShedules as $day)
                            <li class="d-flex align-items-center justify-content-between">
                                <span class="day">{{ $day->day }}</span>
                                <span class="time fw-bold">c {{ \Carbon\Carbon::parse($day->open_time)->format('H:i') }} до {{ \Carbon\Carbon::parse($day->close_time)->format('H:i') }}</span>
                            </li>
                        @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h4 class="mb-3" style="font-weight: 500;">Данные организации</h4>
                        <ul class="list-unstyled p-0 m-0">
                            @foreach ($legals as $k => $v)
                            <li class="d-flex align-items-center justify-content-between">
                                <span class="name">{{ $k }}</span>
                                <span class="value fw-bold">{{ $v }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h4 class="mb-3" style="font-weight: 500;">Контактная информация</h4>
                        <ul class="list-unstyled p-0 m-0">
                            <li class="d-flex align-items-center justify-content-between">
                                <span class="name">Телефон</span>
                                <a href="tel:{{ $company->phone }}"><span class="value fw-bold">{{ $company->phone }}</span></a>
                            </li>
                            <li class="d-flex align-items-center justify-content-between">
                                <span class="name">Дополнительный телефон</span>
                                <a href="tel:{{ $company->phone_additional }}"><span class="value fw-bold">{{ $company->phone_additional }}</span></a>
                            </li>
                            <li class="d-flex align-items-center justify-content-between">
                                <span class="name">Адрес</span>
                                <span class="value fw-bold">{{ $company->city }}, {{ $company->street }}, {{ $company->house }}, {{ $company->building }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between">
                                <span class="name">Электронная почта</span>
                                <a href="mailto:{{ $company->email_address }}"><span class="value fw-bold">{{ $company->email_address }}</span></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection