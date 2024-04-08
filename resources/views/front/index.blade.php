@extends('layouts.front')

@section('title', 'Доставка роллов и пиццы')
@section('desc', 'Самая сочная еда на любой повож и без повода вовсе')

@section('content')
    <x-front.stories></x-front.stories>
    <x-front.banner></x-front.banner>
    <x-front.category-bar></x-front.category-bar>
    <x-front.catalog></x-front.catalog>
    <additional></additional>
    <modal></modal>
@endsection