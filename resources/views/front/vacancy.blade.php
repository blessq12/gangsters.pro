@extends('layouts.front')

@section('title', 'Вакансии')
@section('desc', '')

@section('hero')
    <x-front.hero-banner
        title="Вакансии в {{ $company->name }}"
        description="Стань частью нашей дружной команды"
        :overlay="true"
    ></x-front.hero-banner>
@endsection

@section('content')
    <div class="container py-5">
        <div class="row">
            <div class="col">
                asdasdasd
            </div>
        </div>
    </div>
@endsection