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
        <div class="row row-cols-1 row-cols-md-2">
            @foreach ($banners as $banner)
                <div class="col mb-4">
                    <div class="p-3 bg-image position-relative rounded overflow-hidden d-flex align-items-end" style="background: url({{ $banner->image->path }}); min-height: 250px; background-size: cover">
                        <div class="position-absolute h-100 w-100 top-0 " style="left: 0; background: rgba(0,0,0,.4)"></div>
                        <div class="position-absolute" style="right: 16px; top: 16px;">
                            <div class="d-flex">
                                <a href="#" class="btn btn-primary" style="margin-right: 12px"><i class="fa fa-pencil"></i></a>
                                <a href="#" class="btn btn-danger"><i class="fa fa-trash"></i></a>
                            </div>
                        </div>
                        <div class="position-relative text-light">
                            <h4>{{ $banner->header }}</h4>
                            @if ($banner->subheader)
                                <p class="m-0"> {{ $banner->subheader }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection