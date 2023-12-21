@extends('layouts.crm')

@section('title', $product->name)

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('crm.products.showCategory', optional($product->productCategory)->id) }}" class="text-decoration-none text-dark">
                <i class="fa fa-arrow-left"></i>
                Вернуться в категорию
            </a>
        </div>
    </div>
    {{-- images --}}
    <div class="row mb-4">
        <div class="col-12">
            <h5>Фотографии</h5>
            <ul class="d-flex overflow-hidden align-items-center list-unstyled" style="overflow-x: scroll">
                <li style="margin-right: 12px; background:url('//via.placeholder.com/150x150'); height: 150px; width: 150px" class="bg-image rounded"></li>
                <li style="margin-right: 12px; background:url('//via.placeholder.com/150x150'); height: 150px; width: 150px" class="bg-image rounded"></li>
                <li style="margin-right: 12px; background:url('//via.placeholder.com/150x150'); height: 150px; width: 150px" class="bg-image rounded"></li>
                <li style="margin-right: 12px; background:url('//via.placeholder.com/150x150'); height: 150px; width: 150px" class="bg-image rounded"></li>
            </ul>
        </div>
    </div>
    {{-- badges --}}
    <div class="row mb-4">
        <div class="col-12">
            <h5>Бэйджи</h5>
            @php
                $badges = [
                    (object) ['name' => 'Хит', 'uri' => 'hit'],
                    (object) ['name' => 'Острый', 'uri' => 'spicy'],
                    (object) ['name' => 'Есть лук', 'uri' => 'onion'],
                    (object) ['name' => 'Есть чеснок', 'uri' => 'garlic'],
                    (object) ['name' => 'Можно детям', 'uri' => 'kidsAllow'],
                ]
            @endphp
            <ul class="d-flex list-unstyled aign-items-center">
                @foreach ($badges as $badge)
                    <li class="btn btn-outline-dark {{ $product->{$badge->uri} ? 'active' : '' }}" style="margin-right: 6px">
                        {{ $badge->name }}
                    </li>    
                @endforeach
            </ul>
        </div>
    </div>
    {{-- consist --}}
    <div class="row mb-4">
        <div class="col-12">
            <h5>Состав</h5>
            <div class="card p-3 w-50">
                {{ $product->consist }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <h5>Основное</h5>
            <ul class="list-unstyled">
                <li>Цена: <b>{{ $product->price }} руб</b></li>
                <li>Нетто: <b>{{ $product->weight }} гр</b></li>
                <li>Штук в наборе: <b>XX шт</b></li>
                
            </ul>
        </div>
    </div>
@endsection