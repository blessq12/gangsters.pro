@extends('layouts.crm')
@section('title', 'Редактирование ' . $product->name)

@section('content')

    <div class="row row-cols-1 row-cols-md-3">

        <div class="col-12 col-md-8">
            <h5>Основная информация</h5>

        </div>

        <div class="col-12 col-md-4">
            
            <h5>Изображения</h5>
            <form action="{{ route('crm.image.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="file" name="images[]" id="image" multiple class="form-control">
                <button type="submit" class="btn btn-primary mt-2">Добавить фото</button>
            </form>
        </div>

    </div>
@endsection