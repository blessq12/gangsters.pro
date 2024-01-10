@extends('layouts.crm')

@section('title', 'Категория - ' . $category->name )

@section('content')
    @if (session('success'))
        <div class="row">
            <div class="col">
                <div class="alert alert-success">
                    {{session('success')}}
                </div>
            </div>
        </div>
    @endif
    <div class="row mb-2">
        <div class="col-12">
            <a href="{{ route('crm.products.index') }}" class="text-decoration-none text-dark">
                <i class="fa fa-arrow-left" style="margin-right: 6px"></i>
                Назад к категориям
            </a>
        </div>
    </div>
    <div class="row row-cols-1">
        <div class="col mb-2">
            <button type="button" class="btn btn-outline-dark" data-bs-target="#create-product" data-bs-toggle="collapse">
                Создать товар
                <i class="fa fa-plus"></i>
            </button>
            <div class="collapse" id="create-product">
                <div class="card card-body mt-2">
                    <form action="{{ route('crm.products.store') }}" method="post" enctype="multipart/form-data"> @csrf
                        <input type="hidden" name="category_id" value="{{ $category->id }}">
                        <div class="row row-cols-1 row-cols-lg-2">
                            <div class="col mb-2">
                                <div class="form-group">
                                    <label for="name">Название</label>
                                    <input type="text" name="name" id="name" required class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="consist">Состав</label>
                                    <textarea name="consist" name="consist" id="consist" rows="3" class="form-control"></textarea>
                                </div>
                                <div class="row mb-2">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="weigth">Вес нетто (гр)</label>
                                            <input type="text" onkeyup="this.value = this.value.replace(/[^\d+]/g, '')" name="weight" id="weight" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="price">Цена (руб)</label>
                                            <input type="text" onkeyup="this.value = this.value.replace(/[^\d+]/g, '')" name="price" id="price" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none d-lg-flex mb-2 mb-lg-0">
                                    <div class="col">
                                        <button type="submit" class="btn btn-dark">Создать</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group mb-2">
                                    <label for="tags">Тэги ('ctrl' + клик, чтобы выбрать несколько)</label>
                                    <select class="form-select" multiple aria-label="multiple select example" name="tags[]" style="scroll-behavior: smooth">
                                        <option value="hit">Хит</option>
                                        <option value="spicy">Острый</option>
                                        <option value="kidsAllow">Можно детям</option>
                                        <option value="onion">Лук</option>
                                        <option value="garlic">Чеснок</option>
                                      </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="image" class="btn btn-outline-primary w-100">
                                        Добавить фотографии
                                        <i class="fa fa-plus"></i>
                                        <input type="file" name="images[]" id="image" multiple class="d-none" onchange="{
                                            let ul = document.querySelector('#images-preview')
                                            for (e in this.files){
                                                let li = document.createElement('li'),
                                                    img = document.createElement('img')
                                                img.classList.add('img-fluid')
                                                img.classList.add('rounded')
                                                img.src = window.URL.createObjectURL(this.files[e])
                                                li.style.cssText = 'max-width:150px;margin-right:6px; overflow: hidden'
                                                ul.appendChild(li).appendChild(img)
                                            }
                                        }">
                                    </label>
                                    <ul id="images-preview" class="list-unstyled p-0 m-0 d-flex mt-2 align-items-center"></ul>
                                </div>
                                <div class="form-group d-block d-md-none d-lg-none">
                                    <button type="submit" class="btn btn-dark">Создать</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @if ($category->products->isEmpty())
            <div class="col">  
                <p>
                    В категории "{{ $category->name }}" еще нет ни одного товара.
                </p>    
            </div>
        @else
            <div class="col">
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
                                        <img src="{{ optional($product->thumbSmall()->first())->path ? optional($product->thumbSmall()->first())->path : '//via.placeholder.com/128x128' }}" style="width: 50px; height: 50px" alt="" class="img-fluid rounded">
                                    @else
                                        <img src="//via.placeholder.com/128x128" style="width: 50px; height: 50px" alt="" class="img-fluid rounded">
                                    @endif
                                </td >
                                <td style='vertical-align:middle'>{{ $product->name }}</td>
                                <td style='vertical-align:middle'>
                                    <form action="{{ route('crm.product.product-visibility', $product->id) }}" method="post"> @csrf @method('PATCH')
                                        <div class="btn-group">
                                            <button type="submit" class="btn btn-outline-dark btn-sm {{ $product->visible ? 'active' : '' }}">Виден</button>
                                            <button type="submit" class="btn btn-outline-dark btn-sm {{ !$product->visible ? 'active' : '' }}">Скрыт</button>
                                        </div>
                                    </form>
                                </td>
                                <td style='vertical-align:middle'>
                                    <div class="d-flex align-items-center">
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
        @endif
    </div>
    @if (!$category->products->isEmpty())
        <div class="row row-cols-1">
            <div class="col">
                {{ $products->links() }}
            </div>
        </div>
    @endif
@endsection