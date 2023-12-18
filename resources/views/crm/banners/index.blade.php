@extends('layouts.crm')

@section('title', 'Баннеры')

@section('content')
    <x-alert></x-alert>
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('crm.banners.create') }}" class="btn btn-outline-dark">
                Создать новый 
                <i class="fa fa-plus"></i>
            </a>
        </div>
    </div>
    <div class="row">
        @if ($banners->isEmpty())
            <div class="col-12">
                <p>Нет ни одного активного баннера</p>
            </div>
        @endif
        <div class="col-12">
            @foreach ($banners as $banner)
                <div class="mb-4">
                    {{ $banner }}
                </div>
            @endforeach
        </div>
    </div>
@endsection