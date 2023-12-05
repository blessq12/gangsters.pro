@extends('layouts.crm')

@section('title', 'Пользователи')

@section('content')
    <x-alert></x-alert>
    <div class="row mb-4">
        <div class="col-12">
            <div class="accordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#create">
                            Создать нового пользователя
                            <i class="fa fa-plus px-2"></i>
                        </button>
                    </h2>
                    <div class="accordion-collapse collapse" id="create">
                        <div class="accordion-body">
                            создать нового пользователя
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">

        @if (!count($users))
            <div class="col-12">
                <p>Нет ни одного пользователя</p>
            </div>
        @endif
        <div class="col-12">
            @foreach ($users as $user)
                <div class="mb-4">
                    {{ $user }}
                </div>
            @endforeach
        </div>
    </div>
@endsection