@extends('layouts.front')

@section('title', 'Доставка роллов и пиццы')
@section('desc', 'Самая сочная еда на любой повож и без повода вовсе')

@section('content')
    <x-front.stories></x-front.stories>
    <x-front.banner></x-front.banner>
    <x-front.catalog></x-front.catalog>
    <additional></additional>
    <modal></modal>
    <x-front.bottom-cart></x-front.bottom-cart>
@endsection