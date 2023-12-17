@extends('layouts.front')

@section('title', 'Главная страница')

@section('content')
    @auth
        <x-crm.top-bar></x-crm.top-bar>
    @endauth
    <app
        :goods='@json($goods)'
        :company='@json($company)'
        :stories='@json($stories)'
        :banners='@json($banners)'
    ></app>
@endsection