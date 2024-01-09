@extends('layouts.crm')

@section('title', 'Категории товаров')

@section('content')
    @if (session('success'))
    <div class="row">
        <div class="col">
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        </div>
    </div>
    @endif

    <div class="row mb-4 row-cols-1">
        <div class="col">
            <button type="btn" class="btn btn-outline-dark" data-bs-target="#create-category" data-bs-toggle="collapse">
                Создать категорию
                <i class="fa fa-plus"></i>
            </button>
            <div class="collapse" id="create-category">
                <div class="card card-body mt-2">
                    <form action="{{ route('crm.product.store-new-category') }}" method="post"> @csrf
                        <div class="row row-cols align-items-end">
                            <div class="col">
                                <div class="form-group">
                                    <label for="name">Название категории</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-dark">Создать</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4">
        @foreach ($categories as $category)
        <div class="col mb-4">
            <a href="{{ route('crm.products.showCategory', $category->id) }}" class="text-decoration-none text-dark">
                <div class="border rounded border-dark d-flex overflow-hiddens align-items-end position-relative p-2 p-md-4 h-100" style="min-height: 130px">
                    <div class="position-absolute w-100 h-100 top-0 left-0 d-flex align-items-start justify-content-between p-2">
                        <button class="btn border btn-success btn-sm" title="Количество товаров в категории">
                            {{ $category->products()->count() }}
                        </button>
                        <div class="d-flex">
                            <form action="{{ route('crm.product.update-category-visibility', $category->id) }}" method="post"> @csrf @method('PATCH')
                                <button type="submit" class="btn {{ $category->visible ? 'btn-warning' : 'btn-success' }}  btn-sm" style="margin-right: 6px">
                                    {!! $category->visible ? '<i class="fa fa-eye-slash" title="Скрыть категорию"></i>' : '<i class="fa fa-eye" title="Сделать видимой"></i>' !!}
                                </button>
                            </form>
                            @if ($category->products->isEmpty())    
                                <form action="{{ route('crm.product.destroy-category', $category->id) }}" method="post">@csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Удалить категорию"
                                    onclick="return confirm('Действительно удалить категорию?')"
                                    >
                                    <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <h6 class="fs-6 fw-light mb-0">
                        {{ $category->name }}
                    </h6>
                </div>
            </a>
        </div>
        @endforeach
    </div>

@endsection