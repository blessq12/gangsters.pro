@extends('layouts.front')

@section('title', 'Главная страница')

@section('content')
    <app
        :goods='@json($goods)'
        :company='@json($company)'
    ></app>
    
@endsection