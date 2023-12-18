@extends('layouts.crm')

@section('title', 'Создать новую историю')

@section('content')
@if (session('success'))
    <div class="row">
        <div class="col-12">

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        </div>
    </div>
@endif
<form action="{{ route('crm.stories.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="row row-cols-1">
        <div class="col mb-4">
            <div class="form-group">
                <label for="name">Название</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
        </div>
        <div class="col mb-4">
            <div class="form-group">
                <label for="thumb">Миниатюра</label>
                <input type="file" name="thumb" id="thumb" class="form-control">
            </div>
        </div>
        <div class="col mb-4">
            <div class="form-group">
                <label for="image">Изображение</label>
                <input type="file" name="image" id="image" class="form-control">
            </div>
        </div>
        <div class="col">
            <button type="submit" class="btn btn-dark">Создать</button>
        </div>
    </div>
</form>
    
@endsection