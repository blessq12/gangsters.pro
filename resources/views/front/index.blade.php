@extends('layouts.front')

@section('title', 'Доставка роллов и пиццы')
@section('desc', 'Самая сочная еда на любой повож и без повода вовсе')

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
    <banner></banner>
    <x-front.catalog></x-front.catalog>
    <additional></additional>
    <modal></modal>
    {{-- gangsters cart --}}
    <gc-cart></gc-cart>
@endsection
