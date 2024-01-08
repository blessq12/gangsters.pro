@extends('layouts.crm')

@section('title', 'Категории товаров')

@section('content')

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4">
        @foreach ($categories as $category)
        <div class="col mb-4">
            <a href="{{ route('crm.products.showCategory', $category->id) }}" class="text-decoration-none text-dark">
                <div class="border rounded border-dark d-flex overflow-hiddens align-items-end position-relative p-2 p-md-4 h-100" style="min-height: 100px">
                    <div class="position-absolute w-100 h-100 top-0 left-0 d-flex align-items-start justify-content-end">
                        <button class="btn border btn-success btn-sm">
                            Товаров: 
                            {{ $category->products()->count() }}
                        </button>
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