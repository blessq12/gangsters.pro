@extends('layouts.crm')

@section('title', 'Истории')

@section('content')
    <x-alert></x-alert>
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('crm.stories.create') }}" class="btn btn-outline-dark">
                Создать новую 
                <i class="fa fa-plus mx-2"></i>
            </a>
        </div>
    </div>
    <div class="row row-cols row-cols-md-3">
        @if ($stories->isEmpty())
            <div class="col-12">
                <p>Нет ни одной активной истории</p>
            </div>
        @endif
        @foreach ($stories as $story)
            <div class="col">
                <img src="{{ $story->thumb }}" alt="" class="img-fluid">
                <img src="{{ $story->image }}" alt="" class="img-fluid">
            </div>
        @endforeach
    </div>
@endsection