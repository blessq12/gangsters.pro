@extends('layouts.crm')

@section('title', 'Категория - ' . $category->name )

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('crm.products.index') }}" class="text-decoration-none text-dark">
                <i class="fa fa-arrow-left" style="margin-right: 6px"></i>
                Назад к категориям
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <table class="table table-hover">
                <thead>
                  <tr>
                    <th scope="col">id</th>
                    <th scope="col">Фото</th>
                    <th scope="col">Название</th>
                    <th scope="col">Видимость</th>
                    <th scope="col">Действия</th>
                  </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)    
                        <tr>
                            <th scope="row">{{ $product->id }}</th>
                            <td style='vertical-align:middle'>
                                @if (!$product->images->isEmpty())
                                    <img src="{{ optional($product->thumbs()->first())->path ? optional($product->thumbs()->first())->path : '//via.placeholder.com/128x128' }}" style="width: 50px; height: 50px" alt="" class="img-fluid rounded">
                                @else
                                    <img src="//via.placeholder.com/128x128" style="width: 100px; height: 100px" alt="" class="img-fluid">
                                @endif
                            </td >
                            <td style='vertical-align:middle'>{{ $product->name }}</td>
                            <td style='vertical-align:middle'>{{ $product->visible ? 'Виден' : 'Нет' }}</td>
                            <td style='vertical-align:middle'>
                                <div class="d-flex align-items-center">
                                    <a href="{{ route('crm.products.show', $product->id) }}" class="btn {{ $product->visible ? 'btn-success' : 'btn-warning' }}" title="Изменить видимость товара">
                                        <i class="{{ $product->visible ? 'fa fa-eye' : 'fa fa-eye-slash' }}"></i>
                                    </a>
                                    <a href="{{ route('crm.products.edit', $product->id) }}" class="btn btn-primary mx-2" title="Редактировать товар">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <form action="{{ route('crm.products.destroy', $product->id) }}" method="POST">
                                        @csrf @method('delete')
                                        <button type="submit" class="btn btn-danger" title="Удалить товар (Действие необратимо)" onclick="return confirm('Действительно удалить товар {{ $product->name }}')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                  
                </tbody>
              </table>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            {{ $products->links() }}
        </div>
    </div>
@endsection