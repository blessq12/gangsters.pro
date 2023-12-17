@extends('layouts.crm')

@section('title', 'Заказы')

@section('content')
    <x-alert></x-alert>
    <div class="row mb-4">
        <div class="col-12">
            <div class="accordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#create">
                            Создать новый заказ 
                            <i class="fa fa-plus px-2"></i>
                        </button>
                    </h2>
                    <div class="accordion-collapse collapse" id="create">
                        <div class="accordion-body">
                            Создать новый заказ
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        @if ($orders->isEmpty())
            <div class="col-12">
                <p>Еще нет ни одного заказа</p>
            </div>
        @endif
        <div class="col-12">
            @foreach ($orders as $order)
                <div class="mb-4">
                    {{ $order }}
                </div>
            @endforeach
        </div>
    </div>
@endsection