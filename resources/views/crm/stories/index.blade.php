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
            <div class="col mb-4">
                <div class="d-flex position-relative rounded overflow-hidden" style="min-height: 250px; max-height: 400px">
                    <div class="position-absolute top-0 m-3" style="z-index: 1; right: 0">
                        <div class="d-flex">
                            <a href="#" class="btn btn-primary" style="margin-right: 12px"><i class="fa fa-pencil"></i></a>
                            <a href="#" class="btn btn-danger"><i class="fa fa-trash"></i></a>
                        </div>
                    </div>
                    <div class="position-relative overflow-hidden">
                        <div class="position-absolute" style="left: 0; top: 0; width: 100%;height:100%;background: rgba(0,0,0,.6)"></div>
                        <img src="{{ $story->image }}" alt="" class="img-fluid rounded">
                    </div>
                    <img src="{{ $story->thumb }}" alt="" class="img-fluid position-absolute bottom-0 rounded m-3" style="width: 80%">
                </div>
            </div>
        @endforeach
    </div>
@endsection