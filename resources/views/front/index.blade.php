@extends('layouts.front')

@section('title', 'Главная страница')

@section('content')
    <app
        :goods='@json($goods)'
        :company='@json($company)'
        :stories='@json($stories)'
        :banners='@json($banners)'
    ></app>
    
@endsection