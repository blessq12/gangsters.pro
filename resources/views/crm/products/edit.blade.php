@extends('layouts.crm')
@section('title', 'Редактирование ' . $product->name)

@section('content')

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="row mb-2">
        <div class="col">
            <a href="{{ route('crm.products.showCategory', $product->productCategory->id) }}" class="text-decoration-none text-dark">
                <i class="fa fa-arrow-left" style="margin-right: 6px"></i>
                Назад в категорию
            </a>
        </div>
    </div>
    <div class="row row-cols-1 row-cols-md-1">
        <div class="col mb-4 d-flex" style="overflow: hidden; overflow-x: scroll">        
            <form action="{{ route('crm.image.product-image-upload') }}" method="post" enctype="multipart/form-data" id="upload">
                @csrf
                <input type="hidden" name="prod_id" value="{{ $product->id }}">
                <label for="image" class="btn btn-secondary d-flex align-items-center justify-content-center" style="height: 120px; width: 120px;margin-right: 12px;">
                    <i class="fa fa-plus fa-3x"></i>
                    <input type="file" name="images[]" id="image" style="display: none" onchange="document.querySelector('#upload').submit()" multiple>
                </label>
            </form>
            @if (!$product->images->isEmpty())
                <ul class="list-unstyled d-flex m-0">
                    @foreach ($product->thumbMedium as $e)
                        <li class="bg-image rounded p-2 d-flex justify-content-end" style="height: 120px; width: 120px;margin-right: 12px;background: url({{ $e->path }})">
                            <form action="{{ route('crm.image.image-destroy', $e->id) }}" method="post">
                                @csrf @method('delete') 
                                <input type="hidden" name="instance" value="product">
                                <input type="hidden" name="instance" value="product">
                                <button class="btn-sm btn-danger btn"><i class="fa fa-trash"></i></button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="rounded border border-secondary h-100 text-secondary p-3 d-flex align-items-center" >
                    Нажмите "+" чтобы добавить фотографии
                </div>
            @endif
        </div>
        <div class="col mb-4">
            <h4 class="mb-2">Теги: </h4>
            @php
                $tags = [
                    (object) ['uri' => 'hit', 'name' => 'Хит'],
                    (object) ['uri' => 'spicy', 'name' => 'Острый'],
                    (object) ['uri' => 'kidsAllow', 'name' => 'Можно детям'],
                    (object) ['uri' => 'onion', 'name' => 'Лук'],
                    (object) ['uri' => 'garlic', 'name' => 'Чеснок']
                ]
            @endphp
            <ul class="list-unstyled p-0 m-0 d-flex align-items-center" style="overflow: hidden; overflow-x: scroll;">
                @foreach ($tags as $tag)
                    <li style="margin-right: 12px; white-space: nowrap;">
                        <form action="{{ route('crm.product.update-product-tag', ['id' => $product->id,'tag' => $tag->uri]) }}" method="post"> @csrf @method('PATCH')
                            <button type="submit" class="btn btn-outline-dark {{ $product[$tag->uri] ? 'active' : ''}}">
                                {{ $tag->name }}
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="col mb-4">
            <h4>Видимость:</h4>
            <form action="{{ route('crm.product.product-visibility', $product->id) }}" method="post"> @csrf @method('PATCH')
                <div class="btn-group">
                    <button class="btn btn-outline-dark {{ $product->visible ? 'active' : '' }}" type="submit">Виден</button>
                    <button class="btn btn-outline-dark {{ !$product->visible ? 'active' : '' }}" type="submit">Скрыт</button>
                </div>
            </form>
        </div>
        <div class="col">
            <h4 class="mb-2">Основная информация</h4>
            <div class="row mb-4">
                <div class="col">
                    {!! $product->created_at !== null ? '<p class="mb-0" style="font-size: 10px">Дата создания: ' . $product->created_at->format('Y/m/d H:i:s') . '</p>' : '' !!}
                    {!! $product->updated_at !== null ? '<p class="mb-0" style="font-size: 10px">Дата обновления: ' . $product->updated_at->format('Y/m/d H:i:s') . '</p>' : '' !!}
                    
                </div>
            </div>
            <form action="{{ route('crm.products.update', $product->id) }}" method="post"> @csrf @method('PATCH')
                <div class="row row-cols-1">
                    <div class="col-12 col-lg-6">
                        <div class="form-group mb-2">
                            <label for="name">Название</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ $product->name }}">
                        </div>
                        <div class="form-group mb-2">
                            <label for="consist">Состав</label>
                            <textarea name="consist" id="consist" rows="2" class="form-control">{{ $product->consist }}</textarea>
                        </div>
                        <div class="form-group mb-4">
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="weight">Масса нетто (гр)</label>
                                        <input type="text" name="weight" id="weight" class="form-control" value="{{ $product->weight }}">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="price">Цена (руб)</label>
                                        <input type="text" name="price" id="price" class="form-control" value="{{ $product->price }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-dark">Сохранить</button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection