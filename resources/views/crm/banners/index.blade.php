@extends('layouts.crm')

@section('title', 'Баннеры')

@section('content')
    <x-alert></x-alert>
    <div class="row mb-4">
        <div class="col">
            <p>Изображения автоматически пережимаются до 1920х1080 пикселей (формат изображения 16:9)</p>
            <button type="button" class="btn btn-outline-dark mb-2" data-bs-target="#newBanner" data-bs-toggle="collapse">
                Новый баннер
                <i class="fa fa-plus px-1"></i>
            </button>
            <div class="collapse" id="newBanner">
                <div class="card card-body">
                    <form action="{{ route('crm.banners.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row row-cols">
                            <div class="col">
                                <div class="form-group mb-2">
                                    <label for="header">Заголовок</label>
                                    <input type="text" name="header" id="header" class="form-control" placeholder="Поле не обязательно">
                                </div>
                                <div class="form-group mb-4">
                                    <label for="subheader">Подзаголовок</label>
                                    <input type="text" name="subheader" id="subheader" class="form-control" placeholder="Поле не обязательно">
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary" disabled>Создать</button>
                                </div>
                            </div>
                            <div class="col">
                                <label for="image" class="w-100 btn btn-primary d-block mb-2">
                                        Добавить фото
                                        <i class="fa fa-plus"></i>
                                        <input type="file" name="image" id="image" class="d-none" 
                                            onchange="document.querySelector('#output').style.background = 'url(' + window.URL.createObjectURL(this.files[0]) + ')';document.querySelector('button[type=submit]').disabled = false"
                                        >
                                </label>
                                <div class="w-100 bg-image rounded" style="min-height: 150px" id="output"></div>
                            </div>
                        </div>
                    </form>
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
        <div class="row row-cols-1 row-cols-md-2">
            @foreach ($banners as $banner)
                <div class="col mb-4">
                    <div class="p-3 bg-image rounded overflow-hidden d-flex align-items-start justify-content-end position-relative" style="background: url({{ is_null($banner->image) ? '//via.placeholder.com/512x512' : $banner->image->path }}); min-height: 250px;">
                        <a href="#" class="btn btn-primary" style="margin-right: 6px"><i class="fa fa-pencil"></i></a>
                        <a href="#" class="btn btn-danger"><i class="fa fa-trash"></i></a>
                        <div class="position-absolute p-3 d-flex align-items-end invisible" style="height: 100%; width: 100%; top: 0; left: 0">
                            <div class="text-light visible">
                                <h6 class="mb-1">{{ $banner->header }}</h6>
                                <p class="mb-1" style="font-size: 10px"> {{ $banner->subheader }} </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection