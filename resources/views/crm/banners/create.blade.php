@extends('layouts.crm')

@section('title', 'Создать новый баннер')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <p>Оптимальный размер баннера - 1920x1080px</p>
        </div>
    </div>
    @if (session('success'))
        <div class="row my-4">
            <div class="col-12">
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            </div>
        </div>
    @endif
    <div class="row row-cols-1">
        <div class="col">
            <form action="{{ route('crm.banners.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group mb-4">
                    <label for="header">Заголовок</label>
                    <input type="text" name="header" id="header" class="w-50 form-control" required>
                </div>
                <div class="form-group mb-4">
                    <label for="subheader">Подзаголовок</label>
                    <input type="text" name="subheader" id="subheader" class="form-control w-50" >
                </div>
                <div class="form-group mb-4">
                    <label for="image">Изображение</label>
                    <input type="file" name="image" id="image" class="form-control w-50">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-dark">Создать</button>
                </div>
            </form>
        </div>
    </div>
@endsection