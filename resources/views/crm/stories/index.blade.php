@extends('layouts.crm')

@section('title', 'Истории')

@section('content')
    <x-alert></x-alert>
    <div class="row mb-4">
        <div class="col-12">
            <div class="accordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#create">
                            Создать новую историю
                            <i class="fa fa-plus px-2"></i>
                        </button>
                    </h2>
                    <div class="accordion-collapse collapse" id="create">
                        <div class="accordion-body">
                            Создать новую историю
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        @if ($stories->isEmpty())
            <div class="col-12">
                <p>Нет ни одной активной истории</p>
            </div>
        @endif
        <div class="col-12">
            @foreach ($stories as $story)
                <div class="mb-4">
                    {{ $story }}
                </div>
            @endforeach
        </div>
    </div>
@endsection