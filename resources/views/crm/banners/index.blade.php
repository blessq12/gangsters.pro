@extends('layouts.crm')

@section('title', 'Баннеры')

@section('content')
    <x-alert></x-alert>
    <div class="row mb-4">
        <div class="col-12">
            <div class="accordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#create">
                            Создать новый
                            <i class="fa fa-plus px-2"></i>
                        </button>
                    </h2>
                    <div class="accordion-collapse collapse" id="create">
                        <div class="accordion-body">
                            Новый баннер
                        </div>
                    </div>
                </div>
            </div>
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