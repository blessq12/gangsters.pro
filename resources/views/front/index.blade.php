@extends('layouts.front')

@section('title', 'Gangsters Sushi - Роллы, Суши и Мексиканская Кухня в Томске')
@section('desc', 'Заказывайте вкусные роллы, суши, мексиканские блюда и воки с доставкой в Томске. Адрес: ул. Говорова, 50/1. Быстро и вкусно!')

@section('content')
    @if (session('error'))
        <div class="container pt-4">
            <div class="row">
                <div class="col">
                    <div class="alert alert-danger">{{ session('error') }}</div>
                </div>
            </div>
        </div>
    @endif
    <x-front.stories></x-front.stories>
    <service-advertisment></service-advertisment>
    <banner></banner>
    <x-front.catalog></x-front.catalog>
    <additional></additional>
    <modal></modal>
    {{-- gangsters cart --}}
    <gc-cart></gc-cart>
@endsection
