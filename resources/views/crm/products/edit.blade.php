@extends('layouts.crm')
@section('title', 'Редактирование ' . $product->name)

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
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
            <div class="row mt-4 g-2">
                @foreach ($product->thumbs as $image)
                    <div class="col-6 position-relative mb-1">
                        <div class="position-absolute" style="right: 8px; top: 6px"> 
                            <form action="{{ route('crm.image.destroy', $image->id) }}" method="post">
                                @csrf @method('delete')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </div>
                        <img src="{{ $image->path }}" alt="" class="img-fluid rounded">
                    </div>
                @endforeach
            </div>
        </div>

    </div>
@endsection